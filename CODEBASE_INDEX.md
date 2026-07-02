# Codebase Index

## Overview
This repository contains a lightweight WordPress accessibility plugin and a demo landing page for showcasing its functionality.

## Repository Files

- [index.html](index.html) — Demo website used for GitHub Pages. Includes the multilingual landing page, accessibility toolbar, and the demo UI.
- [style.css](style.css) — Stylesheet for the demo website.
- [simple-high-contrast.php](simple-high-contrast.php) — Main WordPress plugin file. Registers settings, renders the floating toolbar, and handles the accessibility behavior.
- [readme.txt](readme.txt) — WordPress plugin readme content.
- [.gitignore](.gitignore) — Git ignore rules for local build artifacts.

## Plugin Structure

### Main plugin entry
- [simple-high-contrast.php](simple-high-contrast.php)
  - Defines the `Simple_High_Contrast` class.
  - Hooks into WordPress with `admin_menu`, `admin_init`, and `wp_footer`.

### Core plugin responsibilities
- `add_settings_page()` — Adds the plugin settings page to the WordPress admin.
- `settings_init()` — Registers settings and creates the settings fields.
- `sanitize_settings()` — Validates and sanitizes submitted settings.
- `render_floating_toolbar()` — Outputs the floating accessibility toolbar and its JavaScript.

### Settings fields
The plugin supports:
- Toolbar position
- Contrast toggle visibility
- Font-size button visibility
- Toolbar background color
- Custom button text labels

## Demo Site Structure

### Frontend demo page
- [index.html](index.html)
  - Contains the landing page content.
  - Includes the language switcher and translation data.
  - Includes the accessibility toolbar demo controls.

### Demo styles
- [style.css](style.css)
  - Handles layout, typography, buttons, language switcher, and toolbar appearance.

## Where to Change Things

- To change plugin behavior: edit [simple-high-contrast.php](simple-high-contrast.php)
- To change the demo website text: edit [index.html](index.html)
- To change the demo website appearance: edit [style.css](style.css)
- To change plugin packaging notes: edit [readme.txt](readme.txt)
