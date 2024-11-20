<?php

use App\Logger;
use App\Config;

/**
 * Renders the specified component.
 *
 * This function attempts to load and instantiate a class from the given component
 * class name. If the class is found (either directly or by appending the
 * `config\\Components\\` namespace), it will build the component using the
 * provided parameters and return the resulting HTML string. If the class is not
 * found, a warning is logged, and a fallback message is returned.
 *
 * @param string $className The name of the component class to render. This can be
 *                          a fully qualified class name, or just the class name
 *                          within the `config\\Components\\` namespace.
 * @param array  $parameters Optional associative array of parameters to pass
 *                          to the component's build method.
 * @return string The rendered HTML of the component, or a fallback message
 *                if the component class is not found.
 *
 * @example
 * // Rendering a component with parameters
 * $html = component('Button', ['label' => 'Click Me']);
 *
 * @example
 * // Rendering a component without parameters
 * $html = component('Navbar');
 */
function component(string $className, array $parameters = []): string {
    if (class_exists($className) || class_exists($className = 'config\\Components\\' . $className)) {
        $component = new $className();
        return $component->build($parameters);
    }
    Logger::path('warning.log')->warning("`$className` class component is not found.");
    return '<!-- Component not found -->';
}

/**
 * Imports a file (CSS or JS) into the page.
 *
 * This function checks if the requested resource file exists in the public directory.
 * If it does, it generates an HTML `<link>` or `<script>` tag based on the file's
 * extension (`.css` or `.js`). If the file is not found, it returns a comment indicating
 * the missing resource.
 *
 * @param string $path The relative path to the file to import, starting from the
 *                     `public` directory.
 * @return string The HTML markup for importing the file, or a comment indicating
 *                that the resource is missing.
 *
 * @example
 * // Import a CSS file
 * $cssLink = import('resources/main.css');
 *
 * @example
 * // Import a JS file
 * $jsScript = import('resources/app.js');
 *
 * @example
 * // Handling unsupported file types
 * $unsupported = import('assets/image.png');
 */
function import(string $path): string {
    $domain = Config::get('domain');
    if (file_exists(root . '/public/' . $path)) {
        $ext = pathinfo($path, PATHINFO_EXTENSION);

        switch ($ext) {
            case 'css':
                return "\t<link rel='stylesheet' href='$domain/$path'>\n";
            case 'js':
                return "\t<script src='$domain/$path'></script>\n";
            default:
                Logger::path('warning.log')->warning("`$path` Unsupported resource file type: $path");
                return "\t<!-- Unsupported file type: $path -->\n";
        }
    }

    Logger::path('warning.log')->warning("`$path` resource file not found.");
    return "<!-- Resource file not found: $path -->";
}