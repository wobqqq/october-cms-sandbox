---
name: octobercms-tailor-development
description: "Use when creating, editing, or working with October CMS Tailor blueprints, content fields, entry records, streams, structures, globals, singles, or mixins. Activate when the user mentions Tailor, blueprints, content management without code, headless CMS features, or YAML-based content definitions. Also use when working with EntryRecord models or blueprint handles. Do not use for traditional plugin model/controller development."
license: MIT
metadata:
  author: octobercms
---

# October CMS Tailor Development

Tailor is October CMS's headless CMS feature that lets you define content structures using YAML blueprints, without writing any PHP code or database migrations.

## Blueprint Types

There are three top-level blueprint types: **Entry**, **Global**, and **Mixin**.

Entry blueprints have several variants:

Type | Purpose
--- | ---
`entry` | Base content type with no specific behavior
`stream` | Time-stamped ordered collection (like blog posts)
`structure` | Tree/hierarchy of records (like categories)
`single` | Single record per site (like a homepage)
`global` | Single global record (like site settings)
`mixin` | Reusable field groups included by other blueprints

## Blueprint File Location

Blueprints are stored in two locations:

- **App Blueprints** in `app/blueprints/` - globally available (primary location)
- **Theme Blueprints** in `themes/{name}/blueprints/` - only available when that theme is active

```
app/blueprints/
├── blog/
│   ├── post.yaml        ← Stream blueprint
│   └── category.yaml    ← Structure blueprint
├── pages/
│   └── about.yaml       ← Single blueprint
└── globals/
    └── settings.yaml    ← Global blueprint
```

Plugins can also register blueprints in `plugins/acme/blog/blueprints/`.

## Blueprint YAML Structure

Blueprints contain three identifiers: `uuid` (auto-generated if omitted), `handle`, and `type`.

### Stream Blueprint (ordered collection)

```yaml
handle: Blog\Post
type: stream
name: Blog Post
drafts: true

navigation:
    label: Blog
    icon: icon-pencil
    order: 200

fields:
    title:
        label: Title
        type: text
        validation: required

    slug:
        label: Slug
        type: text
        preset:
            field: title
            type: slug

    content:
        label: Content
        type: richeditor
        size: large

    featured_image:
        label: Featured Image
        type: fileupload
        mode: image
        maxFiles: 1

    category:
        label: Category
        type: entries
        source: Blog\Category
        maxItems: 1

    tags:
        label: Tags
        type: entries
        source: Blog\Tag
```

### Structure Blueprint (tree hierarchy)

```yaml
handle: Blog\Category
type: structure
name: Blog Category

navigation:
    parent: Blog\Post
    label: Categories
    icon: icon-folder
    order: 200

fields:
    title:
        label: Title
        type: text
        validation: required

    description:
        label: Description
        type: textarea
```

Secondary navigation is defined by setting `navigation.parent` to a parent blueprint's handle.

### Global Blueprint

```yaml
handle: Blog\Config
type: global
name: Blog Configuration
formSize: large

navigation:
    parent: Blog\Post
    label: Blog Settings
    icon: icon-cog
    order: 300

fields:
    posts_per_page:
        label: Posts Per Page
        type: number
        default: 10

    show_sidebar:
        label: Show Sidebar
        type: switch
        default: true
```

### Single Blueprint

```yaml
handle: Pages\Homepage
type: single
name: Homepage

navigation:
    label: Homepage
    icon: icon-home
    order: 100

fields:
    hero_title:
        label: Hero Title
        type: text

    hero_content:
        label: Hero Content
        type: richeditor
```

### Mixin Blueprint (reusable fields)

```yaml
handle: Blog\MetaFields
type: mixin
name: SEO Meta Fields

fields:
    meta_title:
        label: Meta Title
        type: text

    meta_description:
        label: Meta Description
        type: textarea
        size: small
```

Using a mixin in another blueprint:

```yaml
fields:
    title:
        label: Title
        type: text

    seo:
        type: mixin
        source: Blog\MetaFields
```

## Blueprint Properties

Property | Description
--- | ---
`handle` | Unique blueprint code (required)
`type` | entry/stream/structure/single/global/mixin (required)
`name` | Display label (required)
`fields` | Form field definitions
`groups` | Content group definitions for multiple content types
`drafts` | Enable draft/publish workflow. Default: `false`
`softDeletes` | Enable soft deletion. Default: `true`
`multisite` | Multisite mode. Default: `false`
`pagefinder` | Page finder settings. Default: `true`
`defaultSort` | Default sort column and direction
`showExport` | Show export button. Default: `true`
`showImport` | Show import button. Default: `true`
`modelClass` | Custom PHP model class
`customMessages` | Override UI messages

## Content Groups

Define multiple content types within a single blueprint:

```yaml
handle: Blog\Post
type: stream
name: Blog Post

groups:
    standard_post:
        name: Standard Post
        fields:
            content:
                label: Content
                type: richeditor
    video_post:
        name: Video Post
        fields:
            video_url:
                label: Video URL
                type: text
```

The active group is stored in the `content_group` attribute.

## Common Content Field Types

Type | Description
--- | ---
`text` | Single-line text input
`email` | Email input
`textarea` | Multi-line text area
`number` | Number input
`richeditor` | Rich text editor (HTML)
`markdown` | Markdown editor
`switch` | Toggle on/off
`dropdown` | Dropdown select
`radio` | Radio button group
`checkbox` | Checkbox
`checkboxlist` | Multiple checkboxes
`fileupload` | File upload (supports `mode: image`)
`repeater` | Repeatable field groups
`entries` | Relation to other Tailor entries
`nesteditems` | Nested items belonging exclusively to this record
`datepicker` | Date/time picker
`colorpicker` | Color picker
`codeeditor` | Code editor
`taglist` | Tag list input
`mediafinder` | Media library file picker
`sensitive` | Revealable password field
`pagefinder` | CMS page link selector
`mixin` | Include fields from a mixin blueprint

### Entries Field Properties

Property | Description
--- | ---
`source` | Blueprint handle to relate to (required)
`maxItems` | Maximum number of related entries
`displayMode` | Display as `relation`, `recordfinder`, `taglist`, or `controller`
`conditions` | Raw SQL where clause
`modelScope` | PHP query scope to apply
`inverse` | Inverse relationship definition

## Field Validation

Add validation rules directly in blueprint fields:

```yaml
fields:
    email:
        label: Email
        type: text
        validation: "required|email"

    age:
        label: Age
        type: number
        validation: "required|integer|min:0|max:150"
```

The `unique` rule is auto-configured and does not require a table name.

Context-specific rules: `required:create`, `required:update`.

## Columns and Scopes

Customize the backend list and filter views per field:

```yaml
fields:
    title:
        label: Title
        type: text
        column: true        # Show in list (true, false, invisible, or label string)

        scope: true          # Show in filters (true, false, or label string)

    secret_notes:
        label: Notes
        type: textarea
        column: false        # Hidden from list

```

Or define columns and scopes as separate sections:

```yaml
columns:
    title:
        label: Title
        type: text
        searchable: true
    created_at:
        label: Created
        type: datetime

scopes:
    is_enabled:
        label: Enabled
        type: switch
```

## Querying Tailor Entries

Each blueprint type has its own model class:

Model Class | Blueprint Type
--- | ---
`Tailor\Models\EntryRecord` | entry, stream, structure
`Tailor\Models\SingleRecord` | single
`Tailor\Models\GlobalRecord` | global

```php
use Tailor\Models\EntryRecord;
use Tailor\Models\SingleRecord;
use Tailor\Models\GlobalRecord;

// Get all published posts
$posts = EntryRecord::inSection('Blog\Post')
    ->where('is_enabled', true)
    ->orderBy('published_at', 'desc')
    ->get();

// Get a single entry by slug
$post = EntryRecord::inSection('Blog\Post')
    ->where('slug', $slug)
    ->first();

// Get global values
$config = GlobalRecord::findForGlobal('Blog\Config');
$postsPerPage = $config->posts_per_page;

// Get a single record
$homepage = SingleRecord::findSingleForSection('Pages\Homepage');

// Get structure as tree
$categories = EntryRecord::inSection('Blog\Category')
    ->getNested();

// Extend a Tailor model
EntryRecord::extendInSection('Blog\Post', function ($model) {
    $model->addDynamicMethod('getReadingTime', function () use ($model) {
        return ceil(str_word_count(strip_tags($model->content)) / 200);
    });
});
```

UUID-based lookups: `inSectionUuid()`, `findSingleForSectionUuid()`, `findForGlobalUuid()`.

## Tailor Components in Themes

Use CMS components (defined in the page INI section) to render Tailor content:

### Collection Component

```
url = "/blog"
layout = "default"

[collection posts]
handle = "Blog\Post"
==
{% for post in posts %}
    <h2>{{ post.title }}</h2>
    {{ post.content|raw }}
{% endfor %}

{{ pager(posts) }}
```

### Section Component (for single entries by URL)

```
url = "/blog/:slug"
layout = "default"

[section post]
handle = "Blog\Post"
entrySlug = "{{ :slug }}"
==
<h1>{{ post.title }}</h1>
{{ post.content|raw }}
```

### Global Component

```
url = "/"
layout = "default"

[global config]
handle = "Blog\Config"
==
Posts per page: {{ config.posts_per_page }}
```

## Multisite with Tailor

Blueprints support multisite for managing content across different sites or locales:

```yaml
handle: Blog\Post
type: stream
name: Blog Post
multisite: sync
```

Multisite values:
- `true` - records are unique per site, all fields are translatable
- `false` - multisite is disabled (default)
- `sync` - records are synchronized within the site group
- `all` - records are synchronized across all sites
- `locale` - records are synchronized to sites sharing the same locale

Global blueprints only support `true` or `false`.

## Navigation

Blueprints define navigation using the `navigation` property:

```yaml

# Primary navigation (top-level menu item)

navigation:
    label: Blog
    icon: icon-pencil
    order: 200

# Secondary navigation (under a parent)

navigation:
    parent: Blog\Post
    label: Categories
    icon: icon-folder
    order: 210
```

Special `parent` values:
- Blueprint handle (e.g., `Blog\Post`) - nested under that blueprint
- `settings` - placed in the Settings area
- `content` - placed in the Content area

## Common Pitfalls

- Blueprint handles must be globally unique across the application.
- The `handle` uses backslash notation (`Blog\Post`), not dot notation.
- Use `entries` field type for relations between Tailor entries, not `relation`.
- Tailor auto-generates database tables - you do not write migrations.
- Changes to blueprint fields require running `php artisan tailor:migrate` to update the database schema.
- Use `GlobalRecord::findForGlobal()` for globals, `SingleRecord::findSingleForSection()` for singles.
- Use `drafts: true` on the blueprint to enable draft/publish workflow.
- The `multisite` property defaults to `false` (multisite disabled, content is shared across all sites).
- Blueprints stored in `app/blueprints/` are globally available; those in `themes/` are theme-specific.