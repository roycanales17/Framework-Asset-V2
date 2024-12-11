<?php
	
	use app\Cache;
	use App\Config;
use app\Database;
use App\Logger;
use App\Request;
use app\Session;
use App\Database\Connection;

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
 * @see ../includes
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
function render(string $className, array $parameters = [], Closure|null $children = null, null|array &$events = []): string {
    if (class_exists($className) || class_exists($className = 'includes\\' . $className)) {
        $component = new $className();
        if ($children) {
            if (is_string($content = $children())) {
                $parameters['children'] = $content;
            }
        }
        $events = $component->getEvents();
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
 * $cssLink = import('resources/main.css', 'css');
 *
 * @example
 * // Import a JS file
 * $jsScript = import('resources/app.js', 'js');
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
                return "\t<link rel='icon' href='$domain/$path' type='image/x-icon'>\n";
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
 * @return mixed The configuration value associated with the key, or an empty
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
function config(string $key, string $const = ''): mixed {
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
    return Database::run($query, $binds);
}

/**
 * Creates a response instance with a specified HTTP status code.
 *
 * This function initializes an instance of the `app\Requests\Response` class,
 * allowing the user to build and send HTTP responses. It provides methods
 * for returning JSON or HTML content.
 *
 * Available Methods:
 * - `json(array $data)`: Sends a JSON response.
 *     - `$data` (array): The data to be encoded into JSON.
 * - `html(string $content)`: Sends an HTML response.
 *     - `$content` (string): The HTML content of the response.
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

/**
 * Retrieves the IP address of the user making the request.
 *
 * This function checks several common headers that contain the user's IP address,
 * including `X-Forwarded-For`, `HTTP_X_FORWARDED_FOR`, and `REMOTE_ADDR`. It will return
 * the first valid IP address found.
 *
 * Example Usage:
 * ```php
 * $userIP = IPAddress();
 * echo $userIP;
 * ```
 *
 * @return string The IP address of the user.
 */
function IPAddress(): string {
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    } elseif (!empty($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    }

    return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'Unknown IP';
}

/**
 * Decrypts a gzipped compressed string.
 *
 * This function attempts to decompress a gzipped string using `gzuncompress()`. If decompression
 * fails, it returns `false`. The input string should be in a valid gzipped format.
 *
 * @param string|null $string $string The gzipped string to be decompressed.
 *
 * @return bool|string The decompressed string on success, or `false` if decompression fails.
 */
function decrypt(string|null $string): bool|string {
    $result = @gzuncompress($string);
    if ( $result === false )
        return false;

    return $result;
}

/**
 * Compresses a string using gzip compression.
 *
 * This function compresses the given string using the `gzcompress()` function. The result will
 * be a gzipped compressed version of the input string.
 *
 * @param string|null $string $string The string to be compressed.
 *
 * @return string The gzipped compressed string.
 */
function encrypt(string|null $string): string {
    return gzcompress($string);
}

/**
 * Retrieve a value from the session storage.
 *
 * @param string $name The key of the session variable to retrieve.
 * @return mixed The value of the session variable, or null if not set.
 */
function session(string $name): mixed {
    return Session::get($name);
}

/**
 * Escape special characters in a string for safe usage.
 *
 * @param string $string The input string to escape.
 * @param bool $trim Optional. Whether to trim the string. Default is true.
 * @return string The escaped (and optionally trimmed) string.
 */
function esc(string $string, bool $trim = true): string {
    $string = str_replace(
        [ "\\", "\x00", "\n", "\r", "'", '"', "\x1a" ],
        [ "\\\\", "\\0", "\\n", "\\r", "\\'", '\\"', "\\Z" ],
        $string
    );

    if ($trim) {
        $string = trim($string);
    }

    return $string;
}

/**
 * Encrypts a given string using AES-256-CBC encryption.
 *
 * This function takes a password and a key, hashes the key using SHA-256,
 * generates a random initialization vector (IV), and then encrypts the password
 * with the AES-256-CBC cipher method. The resulting encrypted password is then
 * combined with the IV, encoded in base64, and returned.
 *
 * @param string $password The password to be encrypted.
 * @param string $key The key to be used for encryption. It will be hashed using SHA-256.
 *
 * @return string The base64-encoded encrypted password along with the IV.
 */
function encryptString($password, $key): string {
	$method = 'aes-256-cbc';
	$key = hash('sha256', $key, true);
	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($method));
	$encryptedPassword = openssl_encrypt($password, $method, $key, 0, $iv);
	
	return base64_encode($iv . $encryptedPassword);
}

/**
 * Decrypts a given base64-encoded encrypted string with an IV using AES-256-CBC.
 *
 * This function takes the encrypted password with IV (base64-encoded), extracts
 * the IV, and uses it along with the key (which is hashed with SHA-256) to decrypt
 * the password using the AES-256-CBC cipher method.
 *
 * @param string $encryptedPasswordWithIv The base64-encoded string containing the encrypted password and IV.
 * @param string $key The key to be used for decryption. It will be hashed using SHA-256.
 *
 * @return bool|string The decrypted password if successful, or `false` if decryption fails.
 */
function decryptString($encryptedPasswordWithIv, $key): bool|string {
	$method = 'aes-256-cbc';
	$key = hash('sha256', $key, true);
	$decodedData = base64_decode($encryptedPasswordWithIv);
	$ivLength = openssl_cipher_iv_length($method);
	$iv = substr($decodedData, 0, $ivLength);
	$encryptedPassword = substr($decodedData, $ivLength);
	
	return openssl_decrypt($encryptedPassword, $method, $key, 0, $iv);
}

/**
 * Retrieves a cached value by its key, or returns a default value if the key does not exist.
 *
 * @param string $key The cache key to retrieve.
 * @param mixed $default The default value to return if the key does not exist in the cache.
 * @return mixed The cached value, or the default value if the cache key does not exist.
 */
function cache(string $key, mixed $default = false): mixed {
	return Cache::get($key, $default);
}