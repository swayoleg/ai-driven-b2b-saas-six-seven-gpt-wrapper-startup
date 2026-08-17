/**
 * Build for the public site.
 *
 * The site used to pull Tailwind, Alpine and jQuery straight off CDNs, which
 * cost four render-blocking round trips to four extra origins before the page
 * could paint. Everything now ships as one stylesheet and one script, built
 * here and committed — there is deliberately no build step on deploy, so the
 * artefacts in public/assets/ are part of the repository.
 *
 *   gulp styles    -> public/assets/app.min.css
 *   gulp scripts   -> public/assets/app.min.js
 *   gulp wallets   -> public/assets/wallets.min.js   (support page only)
 *   gulp critical  -> resources/views/partials/critical.css (inlined in <head>)
 *   gulp           -> all of the above, in that order
 *   gulp watch     -> styles + scripts on change
 */

const { src, dest, series, parallel, watch: gulpWatch } = require('gulp');
const concat = require('gulp-concat');
const cleanCss = require('gulp-clean-css');
const terser = require('gulp-terser');
const { execFile } = require('child_process');
const { Transform } = require('stream');
const path = require('path');
const os = require('os');

const DS = 'public/_ds/nocturne-f619cc82-e259-47de-8eed-259febfc742a/styles.css';
const TW_OUT = path.join(os.tmpdir(), 'sixseven-tailwind.css');

/** Where the critical-CSS extractor points its browser. */
const SITE = process.env.SITE_URL || 'http://aib2b.local';

/**
 * Stamp the build output with the current time.
 *
 * gulp copies the mtime of the *first source file* onto whatever it writes, so
 * a freshly rebuilt bundle would come out dated jquery.js's release date and
 * never change. asset_v() cache-busts on filemtime and nginx caches /assets/
 * for a week, so without this a deploy would ship a new bundle behind an old
 * ?v= and nobody would see it until they hard-refreshed.
 */
function stampNow() {
    return new Transform({
        objectMode: true,
        transform(file, _encoding, callback) {
            if (file.stat) {
                file.stat.mtime = new Date();
                file.stat.atime = file.stat.mtime;
            }

            callback(null, file);
        },
    });
}

/**
 * Refresh the Tailwind content dump from the database.
 *
 * Page and Field Note bodies are raw HTML held in MySQL, not in Blade files, so
 * without this every utility class that only appears in CMS content would be
 * purged. See the content globs in tailwind.config.js.
 */
function content(done) {
    execFile('php', ['artisan', 'tailwind:content'], (error, stdout) => {
        if (error) {
            return done(error);
        }

        process.stdout.write(stdout);
        done();
    });
}

/** Compile Tailwind to a temp file so the concat step can pick it up. */
function tailwind(done) {
    execFile(
        'npx',
        ['tailwindcss', '-c', 'tailwind.config.js', '-i', 'resources/css/tailwind.css', '-o', TW_OUT],
        (error, stdout, stderr) => {
            if (error) {
                process.stderr.write(stderr);

                return done(error);
            }

            done();
        }
    );
}

/**
 * One stylesheet.
 *
 * Order is the cascade, and it mirrors what the browser used to end up with:
 * the play CDN appended its <style> to <head> *after* both <link> tags, so
 * Tailwind utilities won every specificity tie and must stay last here too.
 * Fonts first (so @font-face is declared before anything asks for Inter), then
 * the design-system tokens, then the handful of rules Tailwind cannot express,
 * then the utilities.
 */
function styles() {
    return src(['resources/css/fonts.css', DS, 'public/assets/site.css', TW_OUT], { allowEmpty: false })
        .pipe(concat('app.min.css'))
        .pipe(cleanCss({ level: { 1: { specialComments: 0 } } }))
        .pipe(stampNow())
        .pipe(dest('public/assets'));
}

/**
 * One script.
 *
 * The order is load-bearing, not cosmetic:
 *   1. jQuery — app.js opens with a jQuery(function ($) {...}) call;
 *   2. app.js — it registers its Alpine components on the `alpine:init` event,
 *      which Alpine fires during its own initialisation, so the listener has to
 *      be attached before Alpine's runtime runs;
 *   3. Alpine — boots on load and fires alpine:init.
 * Swap 2 and 3 and every x-data component on the site silently fails to
 * register: no error, just dead forms.
 */
function scripts() {
    return src(['node_modules/jquery/dist/jquery.js', 'public/assets/app.js', 'node_modules/alpinejs/dist/cdn.js'])
        .pipe(concat('app.min.js'))
        .pipe(terser())
        .pipe(stampNow())
        .pipe(dest('public/assets'));
}

/**
 * The wallet clipboard helper stays out of the main bundle on purpose: it is
 * pushed onto the scripts stack by pages/default.blade.php only when the page
 * body contains the [wallets] shortcode, which today means /support alone.
 */
function wallets() {
    return src('public/assets/wallets.js')
        .pipe(concat('wallets.min.js'))
        .pipe(terser())
        .pipe(stampNow())
        .pipe(dest('public/assets'));
}

/**
 * Above-the-fold CSS, inlined into <head> so the first paint needs no network.
 *
 * Extracted from the real rendered pages: the built stylesheet is re-parsed by
 * the browser, every rule is tested against the live DOM, and a rule is kept
 * only if something it matches is actually inside the first viewport. See
 * build/critical.js for the details and the list of pages it unions over.
 */
function critical(done) {
    execFile('node', ['build/critical.js'], { env: { ...process.env, SITE_URL: SITE } }, (error, stdout, stderr) => {
        process.stdout.write(stdout);

        if (error) {
            process.stderr.write(stderr);

            return done(error);
        }

        done();
    });
}

function watch() {
    gulpWatch(
        ['resources/css/**/*.css', 'resources/views/**/*.blade.php', DS, 'public/assets/site.css'],
        series(tailwind, styles)
    );
    gulpWatch('public/assets/app.js', scripts);
    gulpWatch('public/assets/wallets.js', wallets);
}

exports.content = content;
exports.tailwind = tailwind;
exports.styles = series(content, tailwind, styles);
exports.scripts = scripts;
exports.wallets = wallets;
exports.critical = critical;
exports.watch = watch;

exports.default = series(content, tailwind, parallel(styles, scripts, wallets), critical);
