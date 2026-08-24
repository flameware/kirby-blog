# AGENTS.md

This file provides guidance to Codex (Codex.ai/code) when working with code in this repository.

## Project Overview

**Massive Void** — a personal blog by 손성기 (Seongki Sohn), built on **Kirby CMS 5** (Plainkit base). Topics covered include UX/product design, heavy metal, F1, NBA, architecture, and personal projects. Content is written in Korean.

## Architecture

### Core Structure

- **kirby/**: Kirby CMS core framework (v5.0.4)
- **site/**: Project-specific files
  - `templates/`: PHP templates (one per page type)
  - `blueprints/`: YAML files defining content fields and Panel UI
  - `snippets/`: Reusable template components (`header.php`, `footer.php`, `blocks/heading.php`, `sitemap.php`)
  - `plugins/`: Third-party Kirby plugins
- **content/**: Flat-file content (YAML frontmatter + Kirby Blocks JSON)
- **assets/**: CSS and static assets (`favicon.svg`)
- **media/**: Auto-generated thumbnails/processed images (do not edit)
- **index.php**: Application entry point

### Page Types / Templates

| Template | URL | Blueprint |
|---|---|---|
| `home.php` | `/` | — |
| `blog.php` | `/blog` | `blog.yml` |
| `blogpost.php` | `/blog/*` | `blogpost.yml` |
| `projects.php` | `/projects` | `projects.yml` |
| `project.php` | `/projects/*` | `project.yml` |
| `about.php` | `/about` | `about.yml` |

### Content Structure

- **Blog posts** (`content/1_blog/N_slug/blogpost.txt`): Fields are `Title`, `Blocks` (Kirby block editor JSON), `Date`, `Tags`, `Description` (optional SEO summary)
- **Projects** (`content/2_projects/N_slug/project.txt`): Image-based project pages
- Content folders are prefixed with numbers to control sort order and visibility (`1_` = listed)
- `.png.txt` / `.jpg.txt` sidecar files store Kirby image metadata

### CSS Architecture

- `assets/css/index.css`: Global styles, CSS custom properties (design tokens), nav, shared components
- `assets/css/templates/*.css`: Per-template stylesheets (auto-loaded via `css("@auto")` in header)
- Responsive: the content column has no breakpoint — `--column` is one fluid value (`min(100% - 40px, max(46.7vw, 360px + 16.7vw))`) that is full-width-minus-20px gutters on phones and `46.7vw` from 1200px up. The `480px` media query is left for grid columns and spacing only. See `docs/adr/0012-fluid-column-width.md`.
- Font: **Gowun Batang** (Korean serif) from Google Fonts
- Design tokens: `--color-background`, `--color-highlight` (yellow), `--color-textcolor`, `--color-secondary`, `--color-link` (orange), `--column` (content column width)

### Plugins

- **kirby-uniform** (`mzur/kirby-uniform ^5.6`): Form handling with spam guards
- **kirby3-redirects** (`bnomei/kirby3-redirects ^5.1`): URL redirect management via Panel
- **kirby-form** / **kirby-flash**: Additional form/flash utilities
- **seo** (`site/plugins/seo`, local): Page methods `metaTitle()`, `metaDescription()`, `metaExcerpt()`, `metaType()`, `metaImageFile()`, `metaCard()` used by `header.php`. See `docs/adr/0005-page-metadata.md` and `docs/adr/0006-social-card.md`.
- **assets** (`site/plugins/assets`, local): Overrides Kirby's `css` component to append `?v=<filemtime>` to stylesheet URLs. See `docs/adr/0010-asset-cache-busting.md`.

**Kirby only reads `api`, `routes`, and `hooks` from `site/config/config.php`** (`AppPlugins::extensionsFromOptions()`). Every other extension — `components`, `pageMethods`, `blueprints` — must be registered from a plugin. Putting `components` in the config file is **silently ignored**: no error, no warning, the default component just keeps running.

**Adding a hand-written plugin — add the `.gitignore` exception in the same commit.** `.gitignore` has `/site/plugins/*` to keep out the kirby-plugin packages Composer installs there, so a new local plugin is invisible to git by default. Every hand-written plugin needs an explicit `!/site/plugins/<name>` line.

This fails silently and is expensive to spot. Kirby resolves an unknown `$page->foo()` as a content field lookup, not an error — so a page method from a missing plugin returns an **empty field** instead of throwing. Locally the file exists and everything passes; in production the tag just renders blank. This is exactly how `seo` shipped broken (fixed in `b07659b`): every page went live with an empty `<title>`.

Verify before deploying:

```bash
git ls-files site/plugins/<name>   # must print the files, not nothing
```

## Development Commands

```bash
composer start       # PHP dev server at localhost:8000
composer install     # Install dependencies
composer update      # Update dependencies
```

PHP requirement: `~8.2 || ~8.3 || ~8.4 || ~8.5`

Dev dependency: `laravel/pint` (PHP code formatter)

## Deployment

**This repository is public.** Never write the deploy host, bare repo path, hook internals, web root, or credentials into a tracked file — that's why `.gitignore` excludes the setup docs. Read the actual addresses from `git remote -v` at the time you need them.

### `origin` has two push URLs

`origin` fetches from GitHub but **pushes to both GitHub and the deploy server**. One `git push` reaches both.

```bash
git remote -v   # fetch: GitHub · push: GitHub + deploy server
```

Consequences, in order of how often they bite:

- **Pushing `main` deploys.** A server-side receive hook checks out `main` and runs `composer install`. You'll see `>>> Deploying main` … `>>> Done` in the push output — that's the deploy log, read it. Only `main` deploys; pushing a feature branch is safe and touches nothing live.
- **`gh pr merge --delete-branch` only cleans GitHub.** The branch stays on the deploy server and accumulates. Use `git push origin --delete <branch>` instead — it reaches both.
- **A failed deploy shows up as `remote:` output, not a failed push.** The push can succeed while the server-side steps error.

### Deploying

```bash
gh pr merge <n> --merge         # merge on GitHub
git checkout main && git pull   # fast-forward local main
git push origin main            # deploy — watch the remote: output
git push origin --delete <branch>   # clean up both remotes
```

Then verify against the live site rather than trusting the push:

```bash
curl -s https://massivevoid.com/ | grep -o 'assets/css[^"]*'
```

### Cache

Stylesheet URLs carry `?v=<filemtime>`, added by the `assets` plugin's `css` component, so a CSS change reaches readers on their next request. Without it a stale stylesheet can survive for days in iOS Safari — an already-open tab restores from memory and never revalidates. See `docs/adr/0010-asset-cache-busting.md`.

`Cache-Control` comes from the tracked `.htaccess`, in three tiers keyed on whether the URL tracks the content: HTML is `no-cache`, `?v=` assets and `/media/**` are `max-age=31536000, immutable`, version-less `/assets/**` gets a revocable `max-age=86400`. Never put `immutable` on a URL that doesn't change with its content. A `.htaccess` syntax error is a site-wide 500 and `apachectl configtest` doesn't read the file — verify with `curl -I` right after deploying. See `docs/adr/0013-cache-control-tiers.md`.

## Key Patterns

- **Blocks-based content**: Blog posts use the Kirby block editor. Templates render with `$page->blocks()->toBlocks()`. Custom block snippets live in `site/snippets/blocks/`.
- **Auto CSS loading**: `css("@auto")` in `header.php` loads a template-matching CSS file automatically (e.g. `blogpost.php` → `assets/css/templates/blogpost.css`).
- **Page metadata**: Templates call `snippet('header')` with no arguments. `header.php` derives `<title>`, `meta description`, and Open Graph tags from the page itself via the `seo` plugin's page methods — never pass a title in.
- **Social card**: `metaCard()` crops the body's first image block to 1200×630 jpg, falling back to `assets/og-default.png`. Use `thumb()`, not `crop()`, when the output format matters — `crop()` silently discards a `format` option. See `docs/adr/0006-social-card.md`.
- **Heading levels**: The title is the page's only `<h1>` and never appears in the body. Body subheads start at `##`, so `#` is never written in content. `h2` carries `--text-subhead`; `h3` is body size + bold. See `docs/adr/0007-heading-levels.md`.
- **Every screen has an `<h1>`**: `home`, `blog`, `projects`, `about` don't draw a title, so they render one as `<h1 class="visually-hidden">` — `$page->title()`, except home which uses `$site->title()`. Never hide it with `display:none`/`visibility:hidden`; that removes it from the accessibility tree too. See `docs/adr/0011-page-title-outline.md`.
- **Type scale**: Every size comes from one fluid unit — `--fluid: clamp(1rem, 0.893rem + 0.476vw, 1.25rem)` (16px at 360px wide → 20px at 1200px). The five tokens are `--fluid` times a rung of a 1.125 ladder, and they are named for their **role**, matching `CONTEXT.md`'s vocabulary: `--text-tag`, `--text-meta` (작성일), `--text-body`, `--text-subhead`, `--text-title`. There is no `html { font-size }` rule and no mobile-only size token — never add a breakpoint to change a size, change the multiplier. See `docs/adr/0008-type-scale.md`.
- **Column width**: One token, `--column`, sets the content column everywhere (`.container`, and the footer on `/projects`). Gutters live in the width, not in `padding` — the column is already inset, so never add left/right padding on top of it. Never add a breakpoint to change the width; move an end value instead. See `docs/adr/0012-fluid-column-width.md`.
- **형광펜 (link highlight)**: hover와 `[aria-current="page"]`가 공유하는 노란 획. `text-decoration`이 아니라 **글자를 감싸는 인라인 상자의 배경**이다 — 기울기는 `linear-gradient(179deg, …)`의 각도, 삐져나옴은 `padding-inline` + 같은 크기의 음수 `margin-inline`. flex 링크(내비게이션·목록·프로젝트 캡션)는 글자를 `<span>`으로 감싸야 마커가 44px 클릭 영역이 아니라 글자에 붙는다. 규칙은 `index.css` 한 곳에만 있다 — 템플릿 CSS에 복제하지 말 것. See `docs/adr/0014-highlight-marker.md`.
- **Navigation**: Built dynamically from `$site->children()->listed()`. A rounded island that sits **outside** the content column, as a direct child of `body`; it takes only the width of its contents and never collapses — there is no hamburger and no toggle script. See `docs/adr/0009-navigation-island.md`.
- **Content listing**: Home page shows 5 latest blog posts and 4 latest projects via `->children()->listed()->limit(N)`.
- **Tags**: Stored as comma-separated strings, split with `->tags()->split()`.
- **Sitemap**: Available via `site/snippets/sitemap.php`.

## Panel

Admin interface at `/panel` — manage pages, drafts, redirects, and uploads.

## Agent skills

### Issue tracker

Issues and specs live as GitHub issues on `flameware/kirby-blog`, managed via the `gh` CLI. See `docs/agents/issue-tracker.md`.

### Triage labels

The five canonical triage roles, used verbatim as GitHub label names. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context — one `CONTEXT.md` and `docs/adr/` at the repo root. See `docs/agents/domain.md`.
