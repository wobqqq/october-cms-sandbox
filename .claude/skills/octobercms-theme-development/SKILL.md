---
name: octobercms-theme-development
description: "Use when creating, modifying, or working with October CMS themes, including pages, layouts, partials, content files, CMS components, Twig markup, or theme assets. Activate when the user mentions themes, Twig templates, CMS pages, page URLs, layout scaffolding, partial rendering, content blocks, or any frontend template task. Do not use for backend admin panel development or plugin PHP code."
license: MIT
metadata:
  author: octobercms
---

# October CMS Theme Development

Themes are file-based and contain all frontend templates. They use Twig for markup and support pages, layouts, partials, and content files.

## Theme Directory Structure

```
themes/mytheme/
├── theme.yaml              ← Theme metadata
├── pages/
│   ├── index.htm
│   ├── blog.htm
│   └── blog/
│       └── post.htm
├── layouts/
│   └── default.htm
├── partials/
│   ├── header.htm
│   ├── footer.htm
│   └── blog/
│       └── post-card.htm
├── content/
│   └── welcome.md
└── assets/
    ├── css/
    ├── js/
    └── images/
```

## Template Structure

CMS templates (pages, layouts, partials) have up to three sections separated by `==`:

1. **Configuration** (INI format) - template parameters. Files start with a `##` line (editor hint; optional when parsing, always written on save).
2. **PHP code** (optional) - server-side logic
3. **Twig markup** - the rendered HTML

```
##
url = "/blog"
layout = "default"
title = "Blog"
==
<?
function onStart()
{
    $this['posts'] = \Acme\Blog\Models\Post::where('is_published', true)->get();
}
?>
==
<h1>Blog</h1>
{% for post in posts %}
    <article>
        <h2>{{ post.title }}</h2>
        {{ post.content|raw }}
    </article>
{% endfor %}
```

## Pages

Pages define URLs and are the entry point for rendering. Configuration properties:

```ini
##
url = "/blog/post/:slug"
layout = "default"
title = "Blog Post"
description = "Displays a single blog post"
hidden = 0

[blogPost]
slug = "{{ :slug }}"
```

- `url` - page URL (required). Supports parameters with `:param` syntax.
- `title` - page title (required).
- `hidden` - hide from backend page lists and non-admin users.
- Optional parameters use `:param?` syntax, with optional defaults: `:param?default`.
- URL parameters support regex validation: `:id|^[0-9]+$`.
- Wildcard parameters use `:slug*` to capture remaining segments.
- Components are attached in the configuration section using `[componentName]`.

### Error Pages

Error pages are defined by their URL, not by filename:

- A page with URL `/404` is displayed when the system can't find a requested page.
- A page with URL `/error` is displayed when an unhandled error occurs (when debug mode is off).

```ini
##
url = "/404"
layout = "default"
title = "Page Not Found"
hidden = 1
```

## Layouts

Layouts wrap pages and define the HTML scaffold:

```
name = "Default"
description = "Default layout"
==
<!DOCTYPE html>
<html>
<head>
    <title>{{ this.page.meta_title ?: this.page.title }} - My Site</title>
    {% meta %}
    {% styles %}
    {% framework extras %}
</head>
<body>
    {% partial "header" %}

    <main>
        {% page %}
    </main>

    {% partial "footer" %}

    {% scripts %}
</body>
</html>
```

Key Twig tags:
- `{% page %}` - renders the page content
- `{% partial "name" %}` - renders a partial
- `{% content "name.md" %}` - renders a content block
- `{% meta %}` - outputs registered meta tags (open graph, etc.)
- `{% styles %}` - outputs registered CSS
- `{% scripts %}` - outputs registered JS
- `{% framework %}` - includes the AJAX framework
- `{% framework extras %}` - includes AJAX with validation, loading indicators, flash messages
- `{% framework vue %}` - includes the Vue 3 library (exposed as `window.Vue`) for CMS components
- `{% vuecomponents %}` - outputs the Vue client factory and any registered CMS Vue components

## Page Execution Lifecycle

1. Layout `onInit()`
2. Page `onInit()`
3. Layout `onStart()`
4. Layout components `onRun()`
5. Layout `onBeforePageStart()`
6. Page `onStart()`
7. Page components `onRun()`
8. Page `onEnd()`
9. Layout `onEnd()`

## Partials

Reusable template fragments. Can accept variables:

```twig
{% partial "blog/post-card" post=post %}
```

Inside the partial:
```twig
<article class="post-card">
    <h3>{{ post.title }}</h3>
    <p>{{ post.excerpt }}</p>
</article>
```

### Composable Partials with Body

Pass markup blocks into partials using `body`:

```twig
{% partial "card" body %}
    <p>Card content here</p>
{% endpartial %}
```

Inside the partial, render the body:
```twig
<div class="card">
    {% placeholder body %}{% endplaceholder %}
</div>
```

### Variable Scope

Use `only` to restrict variable access:

```twig
{% partial "mypartial" foo="bar" only %}
```

### Props and Attributes

Partials can declare typed props and pass remaining attributes:

```twig
{% props color = 'blue', size = 'md' %}

<button class="btn btn-{{ color }} {{ attributes.class }}" {{ attributes.except('class') }}>
    Click
</button>
```

### AJAX Partials

Use `{% ajaxPartial %}` to enable AJAX handlers and self-updating within partials:

```twig
{% ajaxPartial 'counter' %}
    <span>Count: {{ count }}</span>
    <button data-request="onIncrement" data-request-update="{ _self: true }">
        +1
    </button>
{% endajaxPartial %}
```

Supports lazy loading with `{% ajaxPartial 'name' lazy %}`.

### Partial Lifecycle

Partials support `onStart` and `onEnd` lifecycle functions (not just AJAX handlers):

```
==
<?
function onStart()
{
    $this['items'] = \Acme\Blog\Models\Post::limit(5)->get();
}
?>
==
{% for item in items %}
    <li>{{ item.title }}</li>
{% endfor %}
```

## Content Blocks

Static text/HTML/Markdown content that can be edited separately:

```twig
{% content "welcome.md" %}
```

Content files support four extensions:
- `.html` - HTML markup (WYSIWYG editor in backend)
- `.htm` - HTML markup (code editor in backend)
- `.txt` - plain text
- `.md` - Markdown

## CMS Components

Components are PHP classes that provide page variables (data) and AJAX handlers (server-side actions). They are attached to pages in the configuration section. You write your own markup using the variables and handlers they provide.

```ini
url = "/blog"
layout = "default"

[blogPosts]
postsPerPage = 10
sortOrder = "published_at desc"
```

Using component data and handlers in your markup:

```twig
{% for post in posts %}
    <article>
        <h2><a href="{{ post.url }}">{{ post.title }}</a></h2>
        <p>{{ post.excerpt }}</p>
    </article>
{% endfor %}

<button data-request="onLoadMore">Load More</button>
```

**Important**: Always write custom markup using the component's page variables and AJAX handlers. Do NOT default to using `{% component %}` or creating override partials at `partials/{alias}/`. The component's default partials are reference implementations only, not a starting point for customization.

### Built-in CMS Components

October CMS ships with Tailor-related components:

- `collection` - display collections of Tailor entries with querying support
- `section` - define URL sections for single Tailor entries
- `global` - make global Tailor records available
- `resources` - inject assets, variables, and headers
- `sitePicker` - multisite switching tools

### Defining a Component

```php
namespace Acme\Blog\Components;

use Cms\Classes\ComponentBase;

class BlogPosts extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name' => 'Blog Posts',
            'description' => 'Displays a list of blog posts.',
        ];
    }

    public function defineProperties()
    {
        return [
            'postsPerPage' => [
                'title' => 'Posts per page',
                'description' => 'Number of posts to show per page',
                'default' => 10,
                'type' => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'Must be a number',
            ],
            'sortOrder' => [
                'title' => 'Sort order',
                'description' => 'How to sort the posts',
                'type' => 'dropdown',
                'default' => 'published_at desc',
            ],
        ];
    }

    public function getSortOrderOptions()
    {
        return [
            'published_at desc' => 'Newest first',
            'published_at asc' => 'Oldest first',
            'title asc' => 'Title (A-Z)',
        ];
    }

    public function onRun()
    {
        $this->page['posts'] = $this->loadPosts();
    }

    protected function loadPosts()
    {
        return \Acme\Blog\Models\Post::where('is_published', true)
            ->orderByRaw($this->property('sortOrder'))
            ->paginate($this->property('postsPerPage'));
    }

    /**
     * AJAX handler - called via data-request="onLoadMore"
     */
    public function onLoadMore()
    {
        // ...
    }
}
```

### Component Lifecycle

Method | When Called
--- | ---
`init()` | When the component is first initialized
`onRun()` | Before the page is rendered
`onRender()` | Before the component's default partial is rendered
`onSomething()` | AJAX handlers, called on demand

### Component Reference Partials

Components ship with default partials in the plugin directory (e.g. `plugins/acme/blog/components/blogposts/default.htm`). These are reference implementations that show what variables are available and how handlers are called. Look at them to understand the component's API, then write your own markup.

```
plugins/acme/blog/
└── components/
    ├── BlogPosts.php
    └── blogposts/
        └── default.htm    ← Reference only, not used in your theme
```

For quick prototyping, you can render this default markup with `{% component 'blogPosts' %}`, but this is not intended for production themes.

### Component Partial Overriding

In rare cases where you want to use a component's default markup but customize a specific partial within it, you can create a file at `partials/{alias}/partial-name.htm` in your theme. This is a niche feature for components with complex internal structure. In most cases, writing your own markup directly is simpler and more flexible.

### Vue Components

CMS components can render with Vue 3. October ships the library and the wiring; you mount the app.

Scaffold the Vue component class with `create:vuecomponent Acme.Blog PostViewer`. It extends `System\Classes\VueComponentBase` and is three files (PHP class, ESM module `assets/js/<name>.js`, partial `partials/_<name>.php`). The CMS component then registers it in `init()`, which makes it available during both page renders and AJAX requests (before `{% vuecomponents %}` renders):

```php
public function init()
{
    $this->registerVueComponent(\Acme\Blog\VueComponents\PostViewer::class);
}
```

The theme opts in with two tags. `{% framework vue %}` loads the Vue library (exposed as the `window.Vue` global), and `{% vuecomponents %}` loads the client factory plus the registered component templates:

```twig
{% framework vue %}

<div id="app">
    <acme-blog-post-viewer :post-id="7"></acme-blog-post-viewer>
</div>

{% vuecomponents %}

<script type="module">
    oc.mountVueApp('#app');
</script>
```

- Place `{% vuecomponents %}` before your mounting script so the registration module runs first.
- To bring your own Vue (bundle or CDN), omit `{% framework vue %}` and expose it as `window.Vue`.
- Frontend component ESM files must read the `Vue` global (`const { ref } = Vue`), not `import ... from 'vue'`; bare imports only resolve in the backend panel.
- Mounting, turbo re-mounts, and AJAX partial updates inside Vue-managed DOM are the developer's responsibility, using `oc.createVueApp` / `oc.mountVueApp`.

## Twig Reference

### Variables

```twig
{{ variable }}              {# Output escaped #}

{{ variable|raw }}          {# Output unescaped HTML #}

{{ this.page.title }}       {# Current page title #}

{{ this.page.id }}          {# Current page filename #}

{{ this.param.slug }}       {# URL parameter #}

```

### Page Variables

Variable | Description
--- | ---
`this.page` | Current page object (title, url, description, meta_title, etc.)
`this.layout` | Current layout object
`this.theme` | Theme customization values (from theme.yaml form fields)
`this.param` | URL parameters (e.g., `this.param.slug`)
`this.request` | Request info (method, AJAX status, PJAX status)
`this.session` | Session data (`get`, `has`, `put`, `forget`)
`this.site` | Multisite definition (locale, timezone, etc.)
`this.environment` | Application environment
`this.controller` | CMS controller instance

### Filters

```twig
{{ 'hello'|upper }}                    {# HELLO #}

{{ date|date('F j, Y') }}             {# January 1, 2025 #}

{{ html|raw }}                         {# Unescaped output #}

{{ text|e }}                           {# HTML escaped #}

{{ items|length }}                     {# Count #}

{{ 'slug-text'|page }}                 {# Resolve page URL #}

{{ 'image.jpg'|theme }}                {# Theme asset URL #}

{{ 'image.jpg'|media }}                {# Media library URL #}

{{ 'image.jpg'|resize(200, 200) }}     {# Resize image #}

{{ 'path/to/file'|app }}               {# Absolute URL relative to public path #}

{{ text|md }}                          {# Markdown to HTML #}

{{ 'Hello'|trans }}                    {# Translation #}

{{ 'Hello'|_ }}                        {# Translation (shorthand) #}

{{ amount|currency }}                  {# Currency formatting #}

{{ link_value|link }}                  {# Resolve pagefinder links #}

{{ variable|default('fallback') }}     {# Default value for undefined vars #}

```

### Functions

```twig
{# Forms #}

{{ form_open({ request: 'onSubmit' }) }}
{{ form_ajax('onSubmit', { update: { result: '#div' } }) }}
{{ form_close() }}

{# Pagination #}

{{ pager(records) }}
{{ ajaxPager(records, { request: 'onLoadMore' }) }}

{# Rendering #}

{% set html = partial('my-partial', { foo: 'bar' }) %}
{% if hasPartial('optional-partial') %}...{% endif %}
{% set text = content('welcome.md') %}
{% if hasContent('optional.md') %}...{% endif %}
{% set value = placeholder('sidebar') %}
{% if hasPlaceholder('sidebar') %}...{% endif %}

{# Utilities #}

{{ carbon('2025-01-01').diffForHumans() }}
{{ collect([1, 2, 3]).reverse() }}
{{ redirect('/other-page') }}
{{ response('custom body', 200) }}
{{ abort(404) }}
{{ dump(variable) }}

{# Config/Environment #}

{{ config('app.name') }}
{{ env('APP_DEBUG') }}

{# String helpers #}

{{ str_limit('Long text...', 50) }}
{{ html_strip('<p>Hello</p>') }}

{# Page finder links #}

{{ link('october://Blog\\Post/my-slug') }}
```

### Tags

```twig
{% partial "name" variable=value %}
{% content "file.md" %}
{% component "componentAlias" %}
{% page %}
{% meta %}
{% styles %}
{% scripts %}
{% framework %}
{% framework extras %}
{% framework vue %}
{% vuecomponents %}
{% flash %}
    <p class="flash-{{ type }}">
        {{ message }}
    </p>
{% endflash %}
{% placeholder name %}
    Default content here
{% endplaceholder %}
{% put name %}
    Content for placeholder
{% endput %}
{% default %}
    {# Inside {% put %}, positions default placeholder content #}

{% enddefault %}
{% ajaxPartial 'name' %}
    {# Self-updating AJAX partial #}

{% endajaxPartial %}
{% props color = 'blue' %}
{% cache 300 %}
    {# Cached for 300 seconds #}

{% endcache %}
{% verbatim %}
    {# Not parsed by Twig - useful for Vue.js templates #}

{% endverbatim %}
{% macro input(name, value, type) %}
    <input type="{{ type }}" name="{{ name }}" value="{{ value }}" />
{% endmacro %}
```

## PHP Code Section

The PHP code section supports these lifecycle functions:

Function | Available In | Purpose
--- | --- | ---
`onInit()` | Page, Layout | Runs when all components are initialized
`onStart()` | Page, Layout, Partial | Runs before the template is rendered
`onBeforePageStart()` | Layout only | Runs after layout components, before page start
`onEnd()` | Page, Layout, Partial | Runs after the template is rendered
`onSomething()` | Page, Layout, Partial | AJAX handler

```php
function onStart()
{
    $this['activeMenu'] = 'blog';
}
```

Variables set with `$this['name'] = value` become available in Twig as `{{ name }}`.

## Theme Configuration (theme.yaml)

The `theme.yaml` file defines theme metadata and optional backend customization fields:

```yaml
name: My Theme
description: A custom theme
author: Acme
homepage: https://example.com
previewImage: https://example.com/preview.png

require:
    - Acme.Blog
    - RainLab.User

form:
    fields:
        site_name:
            label: Site Name
            type: text
            default: My Website
        logo:
            label: Logo
            type: fileupload
            mode: image
        primary_color:
            label: Primary Color
            type: colorpicker
            default: '#3498db'
            assetVar: primary-color
```

Access theme customization values in Twig:

```twig
<h1>{{ this.theme.site_name }}</h1>
<img src="{{ this.theme.logo.url }}" />
```

The `assetVar` property integrates with the LESS asset combiner to inject variables.

## Child Themes

Child themes inherit from a parent theme and can override specific files:

```yaml

# theme.yaml

name: My Child Theme
parent: mytheme
```

Use `php artisan theme:copy parent child` to create a child theme.

## Snippets

Snippets are configurable content blocks for rich/markdown editors. Create from partials:

```ini
[viewBag]
snippetCode = "blog-list"
snippetName = "Blog List"
snippetDescription = "Displays recent blog posts"

[blogPosts]
postsPerPage = 5
```

## Passing Data Between Layout and Page

Layouts and pages share the same controller instance. Data set in the layout's PHP section is available in the page:

```php
// In layout onStart()
function onStart()
{
    $this['siteSettings'] = \Acme\Blog\Models\Settings::instance();
}
```

```twig
{# Available in any page using this layout #}

{{ siteSettings.site_name }}
```

Pages can also set data for the layout using placeholders:

```twig
{# In page #}

{% put sidebar %}
    <div class="sidebar-content">Custom sidebar</div>
{% endput %}

{# In layout #}

{% placeholder sidebar %}
    <div>Default sidebar</div>
{% endplaceholder %}
```

## Asset Compilation

Combine and minify assets using the `|theme` filter and asset combiner:

```twig
<link href="{{ 'assets/css/style.css'|theme }}" rel="stylesheet" />
<script src="{{ 'assets/js/app.js'|theme }}"></script>

{# Combine multiple files #}

<link href="{{ ['assets/css/reset.css', 'assets/css/style.css']|theme }}" rel="stylesheet" />
```

## Common Pitfalls

- When using components, write your own markup using the page variables and AJAX handlers they provide. Do not use `{% component %}` or create override partials at `partials/{alias}/` unless you have a specific reason.
- Template paths are always absolute from the theme root: `{% partial "blog/post-card" %}` not `{% partial "post-card" %}`.
- The PHP section only allows function definitions and `use` statements - no loose code.
- Use `{{ variable|raw }}` for HTML content, `{{ variable }}` auto-escapes.
- Component aliases in the INI section become the variable name in Twig.
- Pages must have a `url` property - it cannot be empty.
- The `{% framework %}` tag is required for AJAX functionality to work.
- Error pages are defined by URL (`/404`, `/error`), not by filename.
- Use `hidden = 1` (not `is_hidden`) to hide pages from backend lists.
- Theme customization values are accessed via `this.theme.field_name` in Twig, not `this.page`.
- Use `this.page.meta_title` for the HTML `<title>` tag (falls back to `this.page.title`).