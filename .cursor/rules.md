# WordPress Text Escaping Rules

## General Rules

1. Always escape output before displaying it to users
2. Never trust any data, whether it's from the database or user input
3. Escape as late as possible, but always escape
4. Use the most specific escaping function for the context

## Escaping Functions

### Text Translation & Output

-   `esc_html__()` - Use for translating and escaping HTML text

    -   Example: `esc_html__('Your text', 'text-domain')`
    -   Context: Regular text output in HTML
    -   ❌ Don't use: `__()` alone for output

-   `esc_html_e()` - Use for translating, escaping and echoing HTML text
    -   Example: `esc_html_e('Your text', 'text-domain')`
    -   Context: Direct text output in HTML
    -   ❌ Don't use: `_e()` alone for output

### HTML Attributes

-   `esc_attr__()` - Use for translating and escaping attribute values

    -   Example: `esc_attr__('Your text', 'text-domain')`
    -   Context: HTML attribute values that need translation

-   `esc_attr_e()` - Use for translating, escaping and echoing attribute values

    -   Example: `esc_attr_e('Your text', 'text-domain')`
    -   Context: Direct output in HTML attributes

-   `esc_attr()` - Use for escaping attribute values without translation
    -   Example: `esc_attr($value)`
    -   Context: Dynamic HTML attribute values

### URLs

-   `esc_url()` - Use for escaping and displaying URLs

    -   Example: `esc_url($url)`
    -   Context: URLs in HTML output (href, src)
    -   ❌ Don't use: raw URLs in HTML

-   `esc_url_raw()` - Use for escaping URLs for database storage
    -   Example: `esc_url_raw($url)`
    -   Context: Saving URLs to database

### Text Areas

-   `esc_textarea()` - Use for escaping text for textarea fields
    -   Example: `esc_textarea($text)`
    -   Context: <textarea> content

### JavaScript

-   `esc_js()` - Use for escaping strings for JavaScript
    -   Example: `esc_js($text)`
    -   Context: Inline JavaScript strings
    -   ⚠️ Note: Prefer `wp_json_encode()` for JSON data

### SQL

-   `esc_sql()` - Use for escaping SQL queries
    -   Example: `esc_sql($text)`
    -   Context: Raw SQL queries
    -   ⚠️ Note: Prefer `$wpdb->prepare()` for queries

## Context-Specific Rules

1. HTML Content:

    ```php
    // ✅ Correct
    echo esc_html__('Your text', 'text-domain');

    // ❌ Incorrect
    echo __('Your text', 'text-domain');
    ```

2. HTML Attributes:

    ```php
    // ✅ Correct
    <input value="<?php echo esc_attr($value); ?>">

    // ❌ Incorrect
    <input value="<?php echo $value; ?>">
    ```

3. URLs:

    ```php
    // ✅ Correct
    <a href="<?php echo esc_url($url); ?>">

    // ❌ Incorrect
    <a href="<?php echo $url; ?>">
    ```

4. Database Values:

    ```php
    // ✅ Correct
    update_post_meta($post_id, 'key', sanitize_text_field($value));

    // ❌ Incorrect
    update_post_meta($post_id, 'key', $value);
    ```

## Best Practices

1. Always use text domain for translations
2. Use appropriate escaping for the context
3. Don't double escape
4. Don't escape before storing in database
5. Always escape on output
6. Use WordPress functions over PHP native functions

## Security Considerations

1. Never trust user input
2. Never trust database content
3. Escape everything that gets output
4. Use nonces for form submissions
5. Validate before sanitizing
6. Sanitize before escaping

## Common Patterns

1. Form Fields:

    ```php
    <input type="text"
           name="<?php echo esc_attr($name); ?>"
           value="<?php echo esc_attr($value); ?>"
    >
    ```

2. Translated Text:

    ```php
    <h1><?php echo esc_html__('Title', 'text-domain'); ?></h1>
    ```

3. URLs:

    ```php
    <a href="<?php echo esc_url($url); ?>">
       <?php echo esc_html($title); ?>
    </a>
    ```

4. Meta Values:
    ```php
    $value = get_post_meta($post_id, 'key', true);
    echo esc_html($value);
    ```
