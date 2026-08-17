/**
 * Generates the above-the-fold CSS that gets inlined into <head>.
 *
 * Run by `gulp critical`. Writes resources/views/partials/critical.blade.php,
 * which is a *generated* file — edit this script, not that partial.
 *
 * WHY A UNION, AND NOT ONE CRITICAL FILE PER TEMPLATE
 * Page bodies are raw HTML in the database and the layout is shared by every
 * route, so there is no per-template <head> to hang a per-template <style> on
 * without teaching the CMS about build artefacts. The first screens also differ
 * a lot: the homepage opens on the hero and the counter row, /pricing on the
 * plan cards and the monthly/yearly toggle, /blog on the post list. So this
 * unions the critical CSS of one page per distinct first screen, at both a
 * phone and a desktop viewport. The union is a few kB larger than a
 * per-template file would be and covers all of them, which is the right trade
 * when the alternative is a flash of unstyled content on two templates out of
 * three.
 *
 * WHAT IS FORCE-INCLUDED, AND WHY
 * Penthouse keeps a rule when something it matches is inside the first
 * viewport. Four kinds of rule are needed for a correct first paint but are
 * invisible to that test, so they are pinned:
 *   - :root — every colour, radius and spacing token on the site is a custom
 *     property declared there. Drop it and the whole page paints with unset
 *     variables, which is worse than no critical CSS at all.
 *   - the *, ::before, ::after --tw-* defaults Tailwind emits, for the same
 *     reason: the shadow and transform utilities read them.
 *   - [x-cloak] — the attribute that hides Alpine components until Alpine has
 *     booted. If it arrives with the async stylesheet instead of in the inline
 *     block, every x-cloak'd element is briefly visible in its un-initialised
 *     state.
 *   - [data-reveal] — the scroll-reveal opt-in starts at opacity 0. Leaving it
 *     out inverts the flash: above-the-fold content paints, then *disappears*
 *     when the full stylesheet lands, then fades back in when the observer
 *     fires.
 * @font-face is a special case. Penthouse drops any @font-face whose family it
 * cannot find named in the rules it kept — and it never can here, because the
 * design system asks for the font through a custom property
 * (`font-family: var(--font-body)`) rather than by name. forceInclude does not
 * help: it matches selectors, and @font-face has none. So the @font-face blocks
 * are lifted straight out of the built stylesheet and pinned to the front of
 * the critical block by hand. Without them the first paint uses a fallback face
 * and every line of text reflows when the async stylesheet lands.
 */

const fs = require('fs');
const path = require('path');
const penthouse = require('penthouse');
const puppeteer = require('puppeteer-core');

const ROOT = path.resolve(__dirname, '..');
const SITE = process.env.SITE_URL || 'http://aib2b.local';
const CHROME = process.env.CHROME_PATH || '/usr/bin/google-chrome';
const CSS_FILE = path.join(ROOT, 'public/assets/app.min.css');
const OUT = path.join(ROOT, 'resources/views/partials/critical.blade.php');

/** One page per distinct first screen. */
const PAGES = ['/', '/pricing', '/support', '/contact', '/blog', '/blog/suing-the-model'];

/** Phone first — it is the viewport PageSpeed scores. */
const VIEWPORTS = [
    { width: 390, height: 844 },
    { width: 1280, height: 800 },
];

/**
 * Regexes, not strings. Penthouse compares a plain string to the whole selector
 * with ===, so '[data-reveal]' pins the rule that hides a reveal element and
 * NOT '[data-reveal].is-in', the rule that shows it again — which leaves the
 * first paint with every revealable element permanently invisible until the
 * async stylesheet lands. ('*', 'html' and 'body' are pinned by penthouse
 * itself, so they are not repeated here.)
 */
const FORCE_INCLUDE = [
    /^:root$/,
    /^\*, ::before, ::after$/,
    /^\[x-cloak\]$/,
    /^\[data-reveal\]/,
];

/**
 * Penthouse strips these by default. Transitions are put back: [data-reveal]
 * carries the fade the scroll observer triggers, and a reveal that snaps
 * instead of fading on the first screen is a visible regression.
 */
const PROPERTIES_TO_REMOVE = ['cursor', 'pointer-events', '(-webkit-)?tap-highlight-color', '(.*)user-select'];

/**
 * HOW THE RUNS ARE MERGED, AND WHY IT IS NOT JUST A CONCATENATION
 *
 * Penthouse returns reformatted CSS in its own order, and it splits selector
 * lists (a rule written `h1,h2,h3{...}` comes back as `h1,h2{...}` if only two
 * of the three were on screen). Concatenating the output of a dozen runs
 * therefore produces a sheet whose cascade is not the cascade of the sheet it
 * came from — and with equal-specificity rules like `.btn` and `.btn-primary`
 * that is not academic: emitted in the wrong order, `.btn`'s transparent border
 * overrides `.btn-primary`'s accent one and the header button loses its outline
 * for as long as the first paint lasts.
 *
 * So penthouse is used only to decide *which selectors* are critical. The bytes
 * and the order come from the built stylesheet itself: every run contributes
 * selectors to a set, and the source sheet is then walked start to finish and
 * emitted minus everything nobody voted for. The result is a subsequence of
 * app.min.css, so its cascade is identical to the full sheet's by construction.
 */

/**
 * Split a stylesheet into top-level chunks (one rule, or one at-rule with its
 * whole body). Brace counting is enough here: the input is minified CSS from
 * clean-css, so there are no comments and no stray braces outside strings, and
 * the only at-rules the sheet contains are @font-face and @media.
 */
function chunks(css) {
    const out = [];
    let depth = 0;
    let start = 0;
    let inString = false;
    let quote = '';

    for (let i = 0; i < css.length; i++) {
        const c = css[i];

        if (inString) {
            if (c === quote && css[i - 1] !== '\\') {
                inString = false;
            }
            continue;
        }

        if (c === '"' || c === "'") {
            inString = true;
            quote = c;
        } else if (c === '{') {
            depth++;
        } else if (c === '}') {
            depth--;

            if (depth === 0) {
                out.push(css.slice(start, i + 1).trim());
                start = i + 1;
            }
        }
    }

    return out.filter(Boolean);
}

/** `selector{body}` -> `selector`, or an at-rule's prelude. */
function prelude(chunk) {
    return chunk.slice(0, chunk.indexOf('{')).trim();
}

/** `x{...}` -> the text between the outermost braces. */
function inner(chunk) {
    return chunk.slice(chunk.indexOf('{') + 1, chunk.lastIndexOf('}'));
}

/** Whitespace is the only thing that varies between clean-css and css-tree output. */
function key(selector) {
    return selector.replace(/\s+/g, '');
}

/**
 * Every individual selector in a rule, so that a source rule survives when
 * penthouse kept any one member of its selector list.
 */
function selectorKeys(chunk) {
    return prelude(chunk)
        .split(',')
        .map(key)
        .filter(Boolean);
}

/**
 * Record the selectors one penthouse run considered critical. Top-level rules
 * go in `top`; rules inside an @media go in a per-condition set, so a rule that
 * is only critical at one breakpoint does not leak into the other.
 */
function collect(css, votes) {
    for (const chunk of chunks(css)) {
        const head = prelude(chunk);

        if (head.startsWith('@font-face')) {
            continue;
        }

        if (head.startsWith('@')) {
            const condition = key(head);

            if (!votes.media.has(condition)) {
                votes.media.set(condition, new Set());
            }

            for (const nested of chunks(inner(chunk))) {
                for (const selector of selectorKeys(nested)) {
                    votes.media.get(condition).add(selector);
                }
            }

            continue;
        }

        for (const selector of selectorKeys(chunk)) {
            votes.top.add(selector);
        }
    }
}

/**
 * Walk the built stylesheet in order and keep the rules that were voted for,
 * verbatim. @font-face is always kept (see the header comment).
 */
function assemble(cssString, votes) {
    const out = [];

    for (const chunk of chunks(cssString)) {
        const head = prelude(chunk);

        if (head.startsWith('@font-face')) {
            out.push(chunk);
            continue;
        }

        if (head.startsWith('@')) {
            const wanted = votes.media.get(key(head));

            if (!wanted) {
                continue;
            }

            const kept = chunks(inner(chunk)).filter((nested) =>
                selectorKeys(nested).some((selector) => wanted.has(selector))
            );

            if (kept.length) {
                out.push(`${head}{${kept.join('')}}`);
            }

            continue;
        }

        if (selectorKeys(chunk).some((selector) => votes.top.has(selector))) {
            out.push(chunk);
        }
    }

    return out;
}

(async () => {
    const cssString = fs.readFileSync(CSS_FILE, 'utf8');
    const votes = { top: new Set(), media: new Map() };

    for (const page of PAGES) {
        for (const viewport of VIEWPORTS) {
            /**
             * A browser per run. Penthouse leaves enough behind that one shared
             * instance dies part way through a dozen runs, and it dies as an
             * unrelated-looking "Failed to open a new tab" several pages later.
             */
            const browser = await puppeteer.launch({
                executablePath: CHROME,
                args: ['--no-sandbox', '--disable-gpu', '--hide-scrollbars'],
            });

            try {
                const css = await penthouse({
                    url: SITE + page,
                    cssString,
                    width: viewport.width,
                    height: viewport.height,
                    forceInclude: FORCE_INCLUDE,
                    propertiesToRemove: PROPERTIES_TO_REMOVE,
                    timeout: 90000,
                    /**
                     * Penthouse blocks JavaScript by default, which is wrong for
                     * this site: the waitlist wizard, the pricing toggle and the
                     * quote calculator are Alpine components sitting behind
                     * x-cloak, so with JS blocked they are display:none when the
                     * viewport test runs and every .input / .btn / .field rule
                     * they need gets dropped. The first screen of /contact then
                     * paints with an unstyled email field.
                     */
                    blockJSRequests: false,
                    /** Long enough for Alpine to boot and the reveal to settle. */
                    renderWaitTime: 1500,
                    puppeteer: { getBrowser: () => browser },
                });

                const before = votes.top.size;
                collect(css, votes);

                console.log(`  ${page} @ ${viewport.width}px  +${votes.top.size - before} selectors`);
            } finally {
                await browser.close();
            }
        }
    }

    const merged = assemble(cssString, votes);
    const body = merged.join('');

    const header =
        '{{-- GENERATED by `gulp critical` (build/critical.js). Do not edit by hand.\n' +
        `     Above-the-fold CSS for ${PAGES.join(', ')} at ${VIEWPORTS.map((v) => v.width + 'px').join(' and ')},\n` +
        '     unioned. Inlined by layouts/site.blade.php; the full stylesheet loads\n' +
        '     asynchronously behind it. --}}\n';

    fs.writeFileSync(OUT, `${header}<style>${body}</style>\n`);

    console.log(`critical: ${merged.length} rules, ${(body.length / 1024).toFixed(1)} KB -> ${path.relative(ROOT, OUT)}`);
})().catch((error) => {
    console.error(error);
    process.exit(1);
});
