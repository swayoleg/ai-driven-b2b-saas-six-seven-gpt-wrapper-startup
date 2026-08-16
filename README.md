# AI driven b2b SAAS six-seven GPT-wrapper startup

**SIXSEVEN — the agentic substrate for outcome-native enterprises.** Category pending. Definition pending. The product is one model call, wrapped carefully. The marketing site around it is a Laravel 13 application with a translatable CMS, a feature-flagged second language and a committed nginx vhost, which tells you roughly where the effort went.

![Laravel 13](https://img.shields.io/badge/Laravel-13-FF2D20)
![PHP 8.3+](https://img.shields.io/badge/PHP-8.3%2B-777BB4)
![Backpack v7 + PRO v3](https://img.shields.io/badge/Backpack-v7%20%2B%20PRO%20v3-1f2933)
![Build step: none](https://img.shields.io/badge/build%20step-none-6f42c1)
![6/7 score: 4.1](https://img.shields.io/badge/6%2F7%20score-4.1-8b5cf6)

> **This is satire.** Sixseven Technologies, Inc. does not exist. Nothing here is a product, a security, or an offer. See [Disclaimer](#disclaimer).

---

## What this is

A fake enterprise AI vendor, built out to the level of detail that real enterprise AI vendors are built out to, and then one level past that.

The site sells SIXSEVEN: a platform your teams never open, producing outcomes your board can describe with growing confidence. Under the hood it is one model call, wrapped carefully — the site says so on the homepage, in the architecture table, and on slide four of the deck. Nobody has asked a follow-up.

Things the fiction commits to:

- **The 6/7 score.** A single number capturing your organisation's readiness across the dimensions that matter. It is scored out of seven. It has never exceeded six. The seventh dimension is not discussed, because the people who could discuss it are in a meeting.
- **Seven architecture layers**, published in full because transparency is a value and nobody reads past layer three. L3 is *Reasoning core: one model call*. L4 appends your company name to the prompt.
- **A quote calculator** on the pricing page. Move the inputs, the number moves; the relationship between those two facts is proprietary. All quotes are rounded to the nearest 67 and expire when read.
- **Four Field Notes** — a client who bought a second AI to summarise the first one's summaries, an agent given $10,000 that now has $2,000 and a plan, and two others.
- **A waitlist** that issues you a position number which is stable, personal, and entirely made up.
- **An "Invest in us*" page.** The asterisk is the whole product. Valuation $0, equity offered 0.00%, board seats 0. It takes tips, not investment, and says so in seven numbered clauses.

It started life as a static HTML mockup — still in `markup/`, still the input to the seeder — and is now a real CMS-backed Laravel site, so that the copy can be edited by a marketing team that does not exist.

## Screenshots

The homepage, above the fold:

![SIXSEVEN homepage hero](public/uploads/pasted-1786809341773-0.png)

Figure 1 of the homepage — the architecture, to scale:

![The GPT Wrapper, an actual shawarma in branded paper](public/uploads/gpt-wrapper-shawarma.jpg)

More of the hero, including the metrics strip (agents deployed, tokens reconciled, decisions deferred, mean 6/7 score) is in [`public/uploads/`](public/uploads/).

## Stack

| Layer | Choice | Notes |
| --- | --- | --- |
| Framework | Laravel 13, PHP 8.3+ | dev and the vhost run 8.4 |
| Database | MariaDB | translations live in JSON columns |
| Admin | Backpack v7 + PRO v3, Tabler theme | at `/admin`; PRO is paid |
| Templating | Blade | page bodies are raw HTML from the CMS |
| CSS | Tailwind via CDN + a hand-written `site.css` + Nocturne design-system tokens | |
| JS | Alpine.js (CDN), jQuery (CDN), one small `app.js` | |
| i18n | `spatie/laravel-translatable` via Backpack's `HasTranslations` | EN + UK |
| Build step | none | see below |

There is no asset pipeline. Tailwind, Alpine and jQuery come off CDNs; `public/assets/site.css` and `public/assets/app.js` are checked in and served straight off disk. `package.json` and `vite.config.js` are the Laravel skeleton's and nothing in the site uses them — you do not need `npm install`, and you should not run `composer setup` (its script would try to build a frontend that does not exist).

## Quick start

### 0. Prerequisites

PHP 8.3+ with the usual Laravel extensions, Composer, MariaDB (or MySQL), nginx + php-fpm. A **Backpack PRO licence** — see the next step.

### 1. Clone

```bash
git clone git@github.com:swayoleg/ai-driven-b2b-saas-six-seven-gpt-wrapper-startup.git
cd ai-driven-b2b-saas-six-seven-gpt-wrapper-startup
```

### 2. Backpack PRO credentials (`auth.json`)

`backpack/pro` is a paid, private package. `composer install` will fail without credentials, and `auth.json` is gitignored, so you have to recreate it:

```bash
composer config http-basic.backpackforlaravel.com <token-username> <token-password>
```

That writes `auth.json` in the project root. The two values come from your Backpack account, under *Tokens*, and are not in this repository and never will be. If you do not have a licence you can still read the code and run the front end after removing `backpack/pro` from `composer.json` and stripping the PRO field types out of the CRUD controllers, but the admin panel will lose several of its nicer inputs.

### 3. Install and configure

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Then open `.env` and set at least `DB_USERNAME` / `DB_PASSWORD` for your machine, and `ADMIN_PASSWORD` (see step 6).

### 4. Database

```bash
mysql -u root -p -e "CREATE DATABASE aib2b CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
php artisan migrate
php artisan db:seed
php artisan storage:link
```

`db:seed` runs three seeders: the admin user, the site content (parsed out of `markup/`), and the donation wallets — the last of which does nothing until you set the `WALLET_*` variables, see [Wallets](#wallets). Read [Reseeding](#reseeding-is-destructive) before you run it a second time.

### 5. nginx + hosts

A working vhost is committed at [`deploy/nginx/aib2b.local.conf`](deploy/nginx/aib2b.local.conf). It hard-codes an absolute path and a php-fpm socket, so edit `set $root_path` to your checkout's `public/` directory and check the `fastcgi_pass` socket matches your PHP version, then:

```bash
sudo cp deploy/nginx/aib2b.local.conf /etc/nginx/sites-available/
sudo ln -s /etc/nginx/sites-available/aib2b.local.conf /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
echo "127.0.0.1 aib2b.local" | sudo tee -a /etc/hosts
```

The site is then at <http://aib2b.local>. For a quick look without touching nginx, `php artisan serve` works too — everything is served out of `public/`.

### 6. Admin

Log in at <http://aib2b.local/admin>:

| Field | Value |
| --- | --- |
| Email | `admin@aib2b.local` |
| Password | whatever you put in `ADMIN_PASSWORD` |

If `ADMIN_PASSWORD` is empty, `AdminUserSeeder` falls back to `Sixseven!67`. Change it. It is in a public repository, on a site whose stated uptime is 99.67% and whose stated answer rate is 0%.

## How the CMS works

Two content entities, plus three operational ones, all editable at `/admin`.

| Entity | Admin | What it holds |
| --- | --- | --- |
| `Page` | Pages | one row per URL; translatable |
| `Post` | Field Notes | the blog; translatable |
| `Wallet` | Wallets | tip addresses on the support page |
| `Subscriber` | Subscribers | newsletter signups |
| `WaitlistSubmission` | Waitlist | the "request access" wizard |

### Pages

A `Page` has a `slug` and a `template`:

- **`default`** — the `content` field is the entire `<main>` element, stored as raw HTML and echoed unescaped into the layout. This is how five of the six pages work. It is not a block editor, a page builder, or a WYSIWYG. It is a `<textarea>` in a monospace font, which is honest about what it is.
- **`blog`** — renders `eyebrow` / `title` / `lead`, then the published Field Notes, then whatever is in `content` (the newsletter card).

Routing is a single catch-all in [`routes/web.php`](routes/web.php): `/{slug}` resolves to the active page with that slug, `/` resolves to the page slugged `home`, and `/blog/{slug}` resolves to a post. The regex excludes `admin` and `uk`, which is the entire access-control story for the front end.

### Translations

`Page` and `Post` use `spatie/laravel-translatable` through Backpack's `HasTranslations` trait. Every translatable column is a **JSON column** holding `{"en": ..., "uk": ...}` — one row per record, not one row per language. Adding a language means adding it to `locales` in `config/backpack/crud.php`, filling in the fields in the admin, and adding UI strings to `lang/`. Nothing is duplicated, nothing needs a `translations` table, and nothing has to be kept in sync by hand.

### The Ukrainian site

English lives at `/`, Ukrainian at `/uk`. Ukrainian is currently **off**, behind `SITE_UK_ENABLED` in `.env` (default `false`). While the flag is off the `/uk` routes are never registered at all — `/uk/*` is a genuine 404, not a half-translated page — and the EN / УКР switcher is hidden.

The translations themselves are still in the database and still editable in the admin. The language is one environment variable away from coming back; what it is waiting on is the translations being good, which is a smaller problem than it has been made to sound.

### Forms

The waitlist and newsletter forms post to `/waitlist` and `/newsletter` via `fetch()` with a CSRF header (they live inside CMS content, so they cannot use `@csrf`), rate-limited to 5/minute. Submissions are stored and readable in the admin. The waitlist still returns a queue position derived from a hash of your email address — stable, personal, entirely made up — the difference now being that it is issued by a server.

### Wallets

The support page's tip addresses come from the `wallets` table, editable in the admin, so a rotated address does not require a deploy. Where the page body contains the literal string `[wallets]`, the default template swaps it for the rendered list and loads the copy-to-clipboard script — a one-shortcode templating language, which is one more than the platform it is advertising has.

The addresses themselves are **not in this repository**. `WalletSeeder` reads them from the environment via `config/wallets.php`, so set the `WALLET_*` variables listed in `.env.example` on the deploy target and run:

```bash
php artisan db:seed --class=WalletSeeder
```

Entries left blank are skipped, so a clone without those variables gets no wallets rather than a card full of empty rows. The seeder matches on name + network, so changing an address in `.env` and re-running updates the existing row instead of adding a second one.

The addresses are real. They are also, per the Letter of Rights on that page, irreversible, unrefundable and unacknowledged.

## Reseeding is destructive

`SiteContentSeeder` re-parses the original static files in `markup/Joke AI startup satire site/`, extracts each page's `<main>`, `<title>` and meta description, rewrites the `.html` links to app URLs, and writes the result over the matching rows via `updateOrCreate`.

That means **`php artisan db:seed` restores the original content and silently overwrites anything you edited in the admin.** It is exactly what you want when you have broken a page's HTML and exactly what you do not want on a Tuesday afternoon. It is the one destructive command in the repo, and it is spelled the same as the harmless one.

## Project layout

```
app/
  Http/Controllers/SiteController.php   pages + posts, ~40 lines
  Http/Controllers/FormController.php   waitlist + newsletter
  Http/Controllers/Admin/               five Backpack CRUD controllers
  Http/Middleware/SetLocale.php         locale from the first URL segment
  Models/                               Page, Post, Wallet, Subscriber, WaitlistSubmission
  helpers.php                           loc_url(), alt_locale_url()
config/site.php                         the SITE_UK_ENABLED flag
database/seeders/SiteContentSeeder.php  markup/ -> CMS importer
deploy/nginx/aib2b.local.conf           the local vhost, commented
lang/uk.json                            UI strings (chrome, not content)
markup/Joke AI startup satire site/     the original static mockup, EN + UK
public/assets/                          site.css, app.js, wallets.js
public/_ds/                             Nocturne design-system tokens
public/uploads/                         images
resources/views/                        layout, two page templates, post, partials
routes/web.php                          the whole front end
```

## Roadmap

Ordered by likelihood, which is not the same as by value.

- Rework the Ukrainian translations and flip `SITE_UK_ENABLED` back on.
- Deploy to **ai-driven-b2b-saas.com** with real TLS and a production vhost.
- Move the remaining hard-coded homepage numbers (agents deployed, tokens reconciled) into the CMS, so the metrics can be revised without a deploy, the way they are elsewhere.
- Tests. There is a `tests/` directory and an intention.
- A build step, if the CDN ever becomes a problem. It has not become a problem.
- Dimension seven —

## Author

Oleg — [github.com/swayoleg](https://github.com/swayoleg)

## Licence

The application code is MIT. The site's copy, the Field Notes and the design are the author's; take the code, take the ideas, and write your own jokes.

## Disclaimer

This is a work of satire. **SIXSEVEN / Sixseven Technologies, Inc. is not a real company.** There is no product, no platform, no cohort, no waitlist, no round, no cap table and no seventh dimension. The customers, the testimonials, the logos, the team, the 67 open roles and the compliance attestations are all invented, and any resemblance to a real company, person or Series B is coincidental and, statistically, unavoidable.

The "Invest in us" page accepts **tips, not investments**. Nothing on it is a security, a share, a SAFE, a note, a token, a right to a future token, or a discount on any of the above. Sending money to any address on this site buys the author a coffee and creates no instrument, no obligation and no relationship. It is a tip, not a term sheet. Executed by nobody. Enforceable against nobody. Version 6.7.
