# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

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

- **Blog posts** (`content/1_blog/N_slug/blogpost.txt`): Fields are `Title`, `Blocks` (Kirby block editor JSON), `Date`, `Tags`
- **Projects** (`content/2_projects/N_slug/project.txt`): Image-based project pages
- Content folders are prefixed with numbers to control sort order and visibility (`1_` = listed)
- `.png.txt` / `.jpg.txt` sidecar files store Kirby image metadata

### CSS Architecture

- `assets/css/index.css`: Global styles, CSS custom properties (design tokens), nav, shared components
- `assets/css/templates/*.css`: Per-template stylesheets (auto-loaded via `css("@auto")` in header)
- Responsive: desktop at `46.7vw` container width; mobile breakpoint at `480px` with hamburger nav
- Font: **Gowun Batang** (Korean serif) from Google Fonts
- Design tokens: `--color-background`, `--color-highlight` (yellow), `--color-textcolor`, `--color-secondary`, `--color-link` (orange)

### Plugins

- **kirby-uniform** (`mzur/kirby-uniform ^5.6`): Form handling with spam guards
- **kirby3-redirects** (`bnomei/kirby3-redirects ^5.1`): URL redirect management via Panel
- **kirby-form** / **kirby-flash**: Additional form/flash utilities

## Development Commands

```bash
composer start       # PHP dev server at localhost:8000
composer install     # Install dependencies
composer update      # Update dependencies
```

PHP requirement: `~8.2 || ~8.3 || ~8.4 || ~8.5`

Dev dependency: `laravel/pint` (PHP code formatter)

## Key Patterns

- **Blocks-based content**: Blog posts use the Kirby block editor. Templates render with `$page->blocks()->toBlocks()`. Custom block snippets live in `site/snippets/blocks/`.
- **Auto CSS loading**: `css("@auto")` in `header.php` loads a template-matching CSS file automatically (e.g. `blogpost.php` → `assets/css/templates/blogpost.css`).
- **Navigation**: Built dynamically from `$site->children()->listed()`. Mobile hamburger menu toggled via vanilla JS in `header.php`.
- **Content listing**: Home page shows 5 latest blog posts and 4 latest projects via `->children()->listed()->limit(N)`.
- **Tags**: Stored as comma-separated strings, split with `->tags()->split()`.
- **Sitemap**: Available via `site/snippets/sitemap.php`.

## Panel

Admin interface at `/panel` — manage pages, drafts, redirects, and uploads.

## Agent skills

### Issue tracker

Issues and specs live as local markdown files under `.scratch/<feature-slug>/`. See `docs/agents/issue-tracker.md`.

### Triage labels

The five canonical triage roles, used verbatim as `Status:` values in issue files. See `docs/agents/triage-labels.md`.

### Domain docs

Single-context — one `CONTEXT.md` and `docs/adr/` at the repo root. See `docs/agents/domain.md`.
