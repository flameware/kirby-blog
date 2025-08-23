# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a **Kirby CMS Plainkit** - a minimal starter project for the Kirby content management system. Kirby is a file-based CMS that uses plain text files for content storage and PHP templates for rendering.

## Architecture

### Core Structure
- **kirby/**: Contains the Kirby CMS core framework (version 5.0.4)
- **site/**: Your project-specific files
  - `templates/`: PHP template files for rendering pages
  - `blueprints/`: YAML configuration files defining content structure
  - `snippets/`: Reusable template components
- **content/**: Content files (plain text files with frontmatter)
- **media/**: Generated thumbnails and processed images
- **index.php**: Application entry point that bootstraps Kirby

### Content Management
- Content is stored in plain text files with YAML frontmatter
- Each page/content item has a corresponding blueprint YAML file defining its fields
- Templates in `site/templates/` render the content using PHP
- Blueprint files in `site/blueprints/` define the admin panel structure

## Development Commands

### Local Development Server
```bash
composer start
# Starts PHP development server on localhost:8000
```

### PHP Requirements
- PHP 8.1, 8.2, 8.3, or 8.4
- Required extensions: SimpleXML, ctype, curl, dom, filter, hash, iconv, json, libxml, mbstring, openssl

### Composer Commands
```bash
composer install    # Install dependencies
composer update      # Update dependencies
```

## File Organization

### Templates
- `site/templates/default.php`: Default page template
- Templates correspond to page types and render content using Kirby's template API

### Blueprints
- `site/blueprints/site.yml`: Site-level configuration
- `site/blueprints/pages/default.yml`: Default page blueprint with fields definition
- Blueprints define the admin panel interface and available fields

### Content API
- Access page content via `$page->title()`, `$page->text()`, etc.
- Kirby provides extensive template helpers and content methods
- File-based content system with automatic routing

## Key Concepts

- **File-based CMS**: No database required, content stored in text files
- **Template-driven**: PHP templates with Kirby's template API
- **Blueprint configuration**: YAML files define content structure and admin interface
- **Auto-routing**: URL structure matches content folder structure
- **Panel**: Web-based admin interface for content editing (accessible at `/panel`)