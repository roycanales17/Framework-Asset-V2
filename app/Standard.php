<?php

use App\Database\Connection;
use App\DB;
use App\Logger;
use App\Config;
use App\Request;

/**
 * Renders the specified component class and returns the resulting HTML.
 *
 * This function attempts to load and instantiate the specified component class.
 * If the class is found (either directly or by appending the `includes` namespace),
 * it will invoke the component's build method and return the rendered HTML string.
 * If the class is not found, a warning is logged, and a fallback message is returned.
 *
 * @param string $className The name of the component class to render.
 *                          This can be either a fully qualified class name or
 *                          just the class name within the `includes` namespace.
 * @param array  $parameters Optional associative array of parameters to pass
 *                          to the component's `build` method.
 * @param Closure|null $children Optional closure to handle nested content for the component.
 *                               If provided, it will be passed as `children` to the component.
 * @return string The rendered HTML of the component, or a fallback message
 *                if the component class is not found.
 *
 * @see ../includes  // Link to the components directory or class for IDE navigation.
 *
 * @example
 * // Rendering a component with parameters
 * $html = render('Button', ['label' => 'Click Me']);
 *
 * @example
 * // Rendering a component with nested content
 * $html = render('Accordion', [], function() {
 *     return '<div>Nested content</div>';
 * });
 */
function render(string $className, array $parameters = [], Closure|null $children = null): string {
    if (class_exists($className) || class_exists($className = 'includes\\' . $className)) {
        $component = new $className();
        if ($children) {
            if (is_string($content = $children())) {
                $parameters['children'] = $content;
            }
        }
        return $component->build($component->render($parameters));
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
function import(string $path, string $type): string {
    $domain = config('APP_DOMAIN');
    if (file_exists(root . '/public/' . $path)) {

        switch ($type) {
            case 'icon':
                return "\t<link rel='icon' href='$domain/$path'>\n";
            case 'css':
                return "\t<link rel='stylesheet' href='$domain/$path'>\n";
            case 'js':
                return "\t<script src='$domain/$path'></script>\n";
            case 'image':
                return "\t<img src='$domain/$path' alt='Image'>\n";
            case 'video':
                return "\t<video controls src='$domain/$path'></video>\n";
            case 'audio':
                return "\t<audio controls src='$domain/$path'></audio>\n";
            case 'font':
                return "\t<style>@font-face { font-family: 'CustomFont'; src: url('$domain/$path'); }</style>\n";
            case 'iframe':
                return "\t<iframe src='$domain/$path'></iframe>\n";
            default:
                logger::path('warning.log')->warning("`$path` Unsupported resource file type: $path");
                return "\t<!-- Unsupported file type: $path -->\n";
        }
    }

    logger::path('warning.log')->warning("`$path` resource file not found.");
    return "<!-- Resource file not found: $path -->";
}

/**
 * Retrieves a configuration value.
 *
 * This function retrieves a value from the application's configuration using
 * the provided key. Optionally, a constant can be passed for dynamic resolution.
 *
 * @param string $key The configuration key to retrieve.
 * @param string $const (Optional) An additional constant value for lookup.
 * @return string The configuration value associated with the key, or an empty
 *                string if the key does not exist.
 *
 * @example
 * // Retrieve the domain configuration
 * $domain = config('DOMAIN');
 *
 * @example
 * // Retrieve a configuration value with a constant
 * $value = config('SOME_KEY', 'CONSTANT_VALUE');
 */
function config(string $key, string $const = ''): string {
    return Config::get($key, $const);
}

/**
 * Executes a database query with optional parameter bindings.
 *
 * This function is a shorthand for calling the `run` method of the `db` class to execute a raw SQL query.
 * It returns a `Connection` instance that represents the query execution.
 *
 * @param string $query The SQL query string to execute.
 * @param array $binds Optional. The parameter bindings to be passed with the query. Default is an empty array.
 *
 * @return Connection Returns an instance of the `Connection` class.
 */
function db(string $query, array $binds = []): Connection {
    return db::run($query, $binds);
}

/**
 * Creates a response instance with a specified HTTP status code.
 *
 * This function initializes an instance of the `app\Requests\Response` class,
 * allowing the user to build and send HTTP responses. It provides methods
 * for returning JSON or HTML content.
 *
 * Available Methods:
 * - `json(array $data, int $code = 200)`: Sends a JSON response.
 *     - `$data` (array): The data to be encoded into JSON.
 *     - `$code` (int): The HTTP status code for the response. Defaults to 200.
 * - `html(string $content, int $code = 200)`: Sends an HTML response.
 *     - `$content` (string): The HTML content of the response.
 *     - `$code` (int): The HTTP status code for the response. Defaults to 200.
 *
 * Example Usage:
 * ```php
 * response()->json(['message' => 'Success']);
 * response()->html('<h1>Success</h1>');
 * ```
 *
 * @param int $code Optional. The HTTP status code for the response. Default is 200.
 * @return app\Requests\Response Returns an instance of the `app\Requests\Response` class.
 */
function response(int $code = 200): app\Requests\Response {
    return request()->response($code);
}

/**
 * Creates a new instance of the `Request` class to handle incoming HTTP requests.
 *
 * This function initializes a new instance of the `Request` class, which can be used to handle
 * the data from the current HTTP request. The `Request` class provides methods to interact with
 * request data, such as retrieving input values, headers, and other details of the request.
 *
 * Example Usage:
 * ```php
 * // Get a value from the request input
 * $name = request()->input('name');
 * ```
 *
 * @return Request Returns an instance of the `Request` class.
 */
function request(): Request {
    return new Request();
}