# Building the front-end assets

Short version:

```bash
npm install     # once
npm run build   # after changing any CSS or JS
```

---

## Do I need Node?

**To run the site: no.** The built files (`public/assets/app.min.css`, `app.min.js`,
`wallets.min.js`, `public/fonts/`, and the inlined critical CSS in
`resources/views/partials/critical.blade.php`) are all committed to the repository.
A clone, a deploy or a production server needs PHP and MariaDB only — there is no
build step on deploy, and nothing breaks if Node is absent.

**To change CSS or JS: yes.** Node is what rebuilds those committed files. Any
version from 18 up is fine (developed on Node 24 / npm 11).

If you edit a stylesheet or a script and *don't* rebuild, nothing happens — the
browser is served the old bundle. That is the one way to confuse yourself here.

## What the build actually does

Everything the browser loads is built from source into exactly two files, so the
page makes two asset requests instead of six, none of them to a third party.

| Source | Ends up in |
| --- | --- |
| Tailwind utilities (generated) | `public/assets/app.min.css` |
| `public/_ds/nocturne-*/styles.css` (design tokens) | `public/assets/app.min.css` |
| `public/assets/site.css` (hand-written rules) | `public/assets/app.min.css` |
| `resources/css/fonts.css` (self-hosted Inter) | `public/assets/app.min.css` |
| jQuery + `public/assets/app.js` + Alpine | `public/assets/app.min.js` |
| `public/assets/wallets.js` | `public/assets/wallets.min.js` (separate on purpose — only `/support` loads it) |
| above-the-fold CSS, extracted from the live pages | `resources/views/partials/critical.blade.php`, inlined into `<head>` |

Two orderings in `gulpfile.js` are load-bearing and commented there: Tailwind
utilities must come **last** in the CSS (so they still win the specificity ties
they won when the CDN injected them last), and in the JS, jQuery → `app.js` →
Alpine. Swap the last two and every `x-data` component dies silently — no error,
just dead forms.

## Commands

```bash
npm install          # install the toolchain (once, and after pulling a package.json change)
npm run build        # everything: content dump, tailwind, css, js, critical
npm run styles       # CSS only  (content dump + tailwind + concat/minify)
npm run scripts      # JS only
npm run critical     # regenerate the inlined above-the-fold CSS
npm run watch        # rebuild CSS on save while working
```

You can call the gulp tasks directly too — `npx gulp styles`, `npx gulp critical`,
`npx gulp` for the default pipeline. Gulp is local to the project, so there is no
need to install it globally.

## Prerequisites for `critical`

The critical-CSS step is the only one that needs more than the source files: it
loads the real pages in a headless browser and records which rules the first
screen actually uses.

- **The site must be running and reachable.** It hits `http://aib2b.local` by
  default; override with `SITE_URL=https://example.com npm run critical`.
- **Chrome must be installed**, at `/usr/bin/google-chrome` by default; override
  with `CHROME_PATH=/path/to/chrome`.

It visits `/`, `/pricing`, `/support`, `/contact`, `/blog` and one Field Note at
both 390px and 1280px, then inlines the union — one block that is correct for
every template, rather than a per-page block that would need per-page caching.
390px comes first deliberately: mobile is the viewport PageSpeed scores.

If the site is down, this step fails and the rest of the build still succeeds —
you just keep the previous critical CSS. That is safe, only slightly stale.

## The Tailwind trap worth knowing about

Tailwind only ships the classes it can find, and **this site keeps its page
bodies as raw HTML in the database**, where a normal scan cannot see them. Left
alone, Tailwind would purge the classes used in CMS content and quietly break
layouts.

So `php artisan tailwind:content` dumps every page and post body to
`storage/app/tailwind/content.html`, which is one of the paths Tailwind scans
(along with the Blade views, the original `markup/` files and the JS). The build
runs it for you; it is wired into the default gulp task.

The practical consequence: **if you add a Tailwind class while editing a page in
the admin panel, run `npm run build` afterwards**, or that class will not exist
in the stylesheet. Classes that already appear somewhere in `markup/` or the
views are safe without a rebuild.

## When do I need to rebuild?

| You changed | Rebuild? |
| --- | --- |
| `public/assets/site.css` or `app.js` | yes — `npm run build` |
| `public/assets/wallets.js` | yes |
| A Blade view, adding new Tailwind classes | yes |
| Page content in the admin, using a *new* Tailwind class | yes |
| Page content in the admin, ordinary text edits | no |
| Anything above the fold, visually | yes, so the critical CSS matches |
| PHP, routes, models, config | no |

## Cache busting

Handled — `asset_v()` stamps each bundle URL with the file's modification time,
so a rebuilt file gets a new URL automatically. The nginx vhost caches
`/assets/` for a week, and without this you would be hard-refreshing to see your
own changes.

## Committing

Commit the built output. `node_modules/` and the generated
`storage/app/tailwind/` dump are gitignored; `public/assets/*.min.*`,
`public/fonts/*` and `critical.blade.php` are not, on purpose — that is what
keeps deploys free of a build step.
