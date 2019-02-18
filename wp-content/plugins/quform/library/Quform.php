<?php

/**
 * @copyright Copyright (c) 2009-2017 ThemeCatcher (http://www.themecatcher.net)
 */
class Quform
{
    /**
     * Get the URL to the plugin folder
     *
     * @param   string  $path  Extra path to append to the URL
     * @return  string
     */
    public static function url($path = '')
    {
        return Quform::pathExtra(plugins_url(QUFORM_NAME), $path);
    }

    /**
     * Get the URL to the plugin admin folder
     *
     * @param   string  $path  Extra path to append to the URL
     * @return  string
     */
    public static function adminUrl($path = '')
    {
        return Quform::pathExtra(Quform::url('admin'), $path);
    }

    /**
     * Allow users to white-label the plugin name on Quform pages
     *
     * @return string The plugin name
     */
    public static function getPluginName()
    {
        return apply_filters('quform_plugin_name', 'Quform');
    }

    /**
     * Get the IP address of the visitor
     *
     * @return string
     */
    public static function getClientIp()
    {
        $ip = $_SERVER['REMOTE_ADDR'];

        if ( ! empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif ( ! empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            $ip = trim($ips[0]);
        }

        return (string) $ip;
    }

    /**
     * Get the current URL
     *
     * @return string
     */
    public static function getCurrentUrl()
    {
        $url = 'http';
        if (is_ssl()) {
            $url .= 's';
        }
        $url .= '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        return $url;
    }

    /**
     * Get the HTTP referer
     *
     * @return string
     */
    public static function getHttpReferer()
    {
        $referer = '';

        if (isset($_SERVER['HTTP_REFERER'])) {
            $referer = $_SERVER['HTTP_REFERER'];
        }

        return (string) $referer;
    }

    /**
     * Get a property from the current post object
     *
     * @param   string  $property  Which property to get
     * @return  string
     */
    public static function getPostProperty($property = 'ID', $postId = null)
    {
        $post = ! is_null($postId) ? get_post($postId) : get_queried_object();
        $value = '';

        $whitelist = array('ID', 'post_author', 'post_date', 'post_date_gmt', 'post_content', 'post_title',
            'post_excerpt', 'post_status', 'comment_status', 'ping_status', 'post_name', 'to_ping', 'pinged',
            'post_modified', 'post_modified_gmt', 'post_content_filtered', 'post_parent', 'guid', 'menu_order',
            'post_type', 'post_mime_type', 'comment_count'
        );

        if (Quform::isNonEmptyString($property) &&
            in_array($property, $whitelist) &&
            $post instanceof WP_Post &&
            isset($post->{$property})

        ) {
            $value = $post->{$property};
        }

        return (string) $value;
    }

    /**
     * Get the post meta value with the given key from the given post ID or the current post
     *
     * @param   string        $key     The post meta key
     * @param   int|null      $postId  The post ID, if null the current post will be used
     * @return  mixed|string
     */
    public static function getPostMeta($key, $postId = null)
    {
        $post = ! is_null($postId) ? get_post($postId) : get_queried_object();
        $value = '';

        if (Quform::isNonEmptyString($key) && $post instanceof WP_Post) {
            $value = get_post_meta($post->ID, $key, true);
        }

        return $value;
    }

    /**
     * Get a property from the current user object
     *
     * @param   string  $property  Which property to get
     * @return  string
     */
    public static function getUserProperty($property = 'ID')
    {
        $user = wp_get_current_user();
        $value = '';

        // Ensure user_pass is never returned
        $whitelist = array('ID', 'user_login', 'user_nicename', 'user_email', 'user_url', 'user_registered', 'display_name');

        if (Quform::isNonEmptyString($property) &&
            in_array($property, $whitelist) &&
            $user->ID > 0 &&
            isset($user->{$property})
        ) {
            $value = $user->{$property};
        }

        return (string) $value;
    }

    /**
     * Convert the given string to studly case
     *
     * @param  string $value
     * @return string
     */
    public static function studlyCase($value)
    {
        $value = ucwords(str_replace(array('-', '_'), ' ', $value));

        return str_replace(' ', '', $value);
    }

    /**
     * Is the current request a POST request
     *
     * @return bool
     */
    public static function isPostRequest()
    {
        return isset($_SERVER['REQUEST_METHOD']) && strtoupper($_SERVER['REQUEST_METHOD']) === 'POST';
    }

    /**
     * Escaping for strings in HTML
     *
     * Identical to esc_html but with double encoding true
     *
     * @param   string  $value
     * @param   int     $flags
     * @return  string
     */
    public static function escape($value, $flags = ENT_QUOTES)
    {
        $value = wp_check_invalid_utf8($value);

        return _wp_specialchars($value, $flags, false, true);
    }

    /**
     * Sanitize multiple classes
     *
     * @param   string|array  $classes  Classes to sanitize
     * @return  string                  The sanitized classes
     */
    public static function sanitizeClass($classes)
    {
        if (is_array($classes)) {
            $classes = join(' ', $classes);
        }

        $classes = preg_split('/\s+/', trim($classes));

        $sanitizedClasses = array();

        foreach($classes as $class) {
            $sanitizedClass = sanitize_html_class($class);

            if ( ! empty($sanitizedClass)) {
                $sanitizedClasses[] = $sanitizedClass;
            }
        }

        return join(' ', $sanitizedClasses);
    }

    /**
     * Get a value from an array, allowing dot notation
     *
     * @param   array   $array
     * @param   string  $key
     * @param   mixed   $default
     * @return  mixed
     */
    public static function get($array, $key = null, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }

        if (isset($array[$key])) {
            return $array[$key];
        }

        foreach (explode('.', $key) as $segment) {
            if ( ! is_array($array) || ! array_key_exists($segment, $array)) {
                return $default;
            }

            $array = $array[$segment];
        }

        return $array;
    }

    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array   $array
     * @param  string  $key
     * @param  mixed   $value
     * @return array
     */
    public static function set(&$array, $key, $value)
    {
        if (is_null($key)) return $array = $value;

        $keys = explode('.', $key);

        while (count($keys) > 1)
        {
            $key = array_shift($keys);

            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if ( ! isset($array[$key]) || ! is_array($array[$key]))
            {
                $array[$key] = array();
            }

            $array =& $array[$key];
        }

        $array[array_shift($keys)] = $value;

        return $array;
    }

    /**
     * Remove one or many array items from a given array using "dot" notation.
     *
     * @param  array  $array
     * @param  array|string  $keys
     * @return void
     */
    public static function forget(&$array, $keys)
    {
        $original =& $array;

        foreach ((array) $keys as $key)
        {
            $parts = explode('.', $key);

            while (count($parts) > 1)
            {
                $part = array_shift($parts);

                if (isset($array[$part]) && is_array($array[$part]))
                {
                    $array =& $array[$part];
                }
            }

            unset($array[array_shift($parts)]);

            // clean up after each pass
            $array =& $original;
        }
    }

    /**
     * Get a service from the DI container
     *
     * @param   string  $name  The name of the service
     * @return  mixed
     */
    public static function getService($name)
    {
        return $GLOBALS['quform']->getService($name);
    }

    /**
     * Returns true if and only if the given value is a string with at least one character
     *
     * @param   mixed    $value
     * @return  boolean
     */
    public static function isNonEmptyString($value)
    {
        return is_string($value) && $value !== '';
    }

    /**
     * Die and dump arguments, debugging helper method
     */
    public static function dd()
    {
        echo '<pre>';
        foreach (func_get_args() as $arg) {
            var_dump($arg);
        }
        echo '</pre>';
        exit;
    }

    /**
     * Log arguments to the PHP error log
     */
    public static function log()
    {
        foreach (func_get_args() as $arg) {
            ob_start();
            var_dump($arg);
            error_log(ob_get_clean());
        }
    }

    /**
     * Log arguments to the PHP error log only if WP_DEBUG is enabled
     */
    public static function debug()
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            call_user_func_array(array('Quform', 'log'), func_get_args());
        }
    }

    /**
     * Get the length of the given string (multibyte aware)
     *
     * @param   string  $string
     * @return  int
     */
    public static function strlen($string)
    {
        return mb_strlen($string, get_bloginfo('charset'));
    }

    /**
     * Get part of the given string (multibyte aware)
     *
     * @param   string    $string
     * @param   int       $start
     * @param   int|null  $length
     * @return  string
     */
    public static function substr($string, $start, $length = null)
    {
        return mb_substr($string, $start, $length, get_bloginfo('charset'));
    }

    /**
     * Generates an HTML tag
     *
     * @param   string  $tag         The HTML tag
     * @param   array   $attributes  Attributes key => value list for the tag
     * @param   string  $content     Content for non-void elements (not escaped)
     * @return  string
     */
    public static function getHtmlTag($tag, array $attributes = array(), $content = '')
    {
        // https://www.w3.org/TR/html5/syntax.html#void-elements
        $voidElements = array('area', 'base', 'br', 'col', 'embed', 'hr', 'img', 'input', 'keygen', 'link', 'meta', 'param', 'source', 'track', 'wbr');

        $tag = Quform::escape(strtolower($tag));

        $escapedAttributes = array();

        foreach ($attributes as $key => $value) {
            if ($value === true) {
                $escapedAttributes[] = $key;
            } else {
                $escapedAttributes[] = sprintf('%s="%s"', $key, Quform::escape($value));
            }
        }

        $escapedAttributes = count($escapedAttributes) > 0 ? ' ' . implode(' ', $escapedAttributes) : '';

        if (in_array($tag, $voidElements)) {
            $output = sprintf('<%s%s />', $tag, $escapedAttributes);
        } else {
            $output = sprintf('<%1$s%2$s>%3$s</%1$s>', $tag, $escapedAttributes, $content);
        }

        return $output;
    }

    /**
     * Get random bytes with the given $length
     *
     * @param   int     $length
     * @return  string
     */
    public static function randomBytes($length)
    {
        static $passwordHash;

        if ( ! isset($passwordHash)) {
            if ( ! class_exists('PasswordHash')) {
                require_once ABSPATH . WPINC . '/class-phpass.php';
            }

            $passwordHash = new PasswordHash(8, false);
        }

        return $passwordHash->get_random_bytes($length);
    }

    /**
     * Generate a random string with the given $length
     *
     * @param   int     $length
     * @return  string
     */
    public static function randomString($length)
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = Quform::randomBytes($size);

            $string .= substr(str_replace(array('/', '+', '='), '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    /**
     * Set a cookie
     *
     * @param  string  $name        The name of the cookie
     * @param  string  $value       The value of the cookie
     * @param  int     $expire      The time the cookie expires as Unix timestamp
     * @param  bool    $secure      Send the cookie over HTTPS only
     * @param  bool    $logFailure  Make a log entry if the cookie could not be created because headers already sent
     */
    public static function setCookie($name, $value, $expire, $secure, $logFailure = false)
    {
        if ( ! headers_sent()) {
            setcookie($name, $value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure);
        } elseif ($logFailure && defined('WP_DEBUG') && WP_DEBUG) {
            headers_sent($file, $line);
            Quform::log("$name cookie cannot be set - headers already sent by $file on line $line");
        }
    }

    /**
     * Ensure the given number $x is between $min and $max inclusive
     *
     * @param   int  $x
     * @param   int  $min
     * @param   int  $max
     * @return  int
     */
    public static function clamp($x, $min, $max)
    {
        return min(max($x, $min), $max);
    }

    /**
     * Get the given path with $extra appended
     *
     * @param   string  $path   The path
     * @param   string  $extra  Extra path to append to the path
     * @return  string          The combined path (no trailing slash is added)
     */
    public static function pathExtra($path, $extra = '')
    {
        if (Quform::isNonEmptyString($extra)) {
            $path .= '/' . ltrim($extra, '/');
        }

        return $path;
    }

    /**
     * Get a writable temporary directory
     *
     * @param   string  $extra  Extra path to append to the path
     * @return  string          Path without trailing slash
     */
    public static function getTempDir($extra = '')
    {
        return Quform::pathExtra(untrailingslashit(Quform::wpGetTempDir()), $extra);
    }

    /**
     * This is a duplicate of the WP function get_temp_dir() because there was an issue with one
     * popular plugin manually firing the wp_ajax_* hooks before WordPress does,
     * causing this plugin to fatal error since this function was not available
     * at that time. So we'll just use the function below in all cases instead of the
     * WP function.
     *
     * @return string
     */
    private static function wpGetTempDir()
    {
        static $temp = '';
        if ( defined('WP_TEMP_DIR') )
            return trailingslashit(WP_TEMP_DIR);

        if ( $temp )
            return trailingslashit( $temp );

        if ( function_exists('sys_get_temp_dir') ) {
            $temp = sys_get_temp_dir();
            if ( @is_dir( $temp ) && wp_is_writable( $temp ) )
                return trailingslashit( $temp );
        }

        $temp = ini_get('upload_tmp_dir');
        if ( @is_dir( $temp ) && wp_is_writable( $temp ) )
            return trailingslashit( $temp );

        $temp = WP_CONTENT_DIR . '/';
        if ( is_dir( $temp ) && wp_is_writable( $temp ) )
            return $temp;

        return '/tmp/';
    }

    /**
     * Get the URL to the WP uploads directory
     *
     * @param   string  $extra  Extra path to append to the path
     * @return  string
     */
    public static function getUploadsUrl($extra = '')
    {
        $uploads = wp_upload_dir();

        return Quform::pathExtra($uploads['baseurl'], $extra);
    }

    /**
     * Get the absolute path to the WordPress upload directory. If the path is not writable it will return false.
     *
     * @param   string        $extra  Extra path to append to the path
     * @return  string|false          The upload path or false on failure
     */
    public static function getUploadsDir($extra = '')
    {
        $uploads = wp_upload_dir();

        if ($uploads['error'] !== false) {
            return false;
        }

        return Quform::pathExtra($uploads['basedir'], $extra);
    }

    /**
     * Is PCRE compiled with Unicode support?
     *
     * @return bool
     */
    public static function hasPcreUnicodeSupport()
    {
        static $hasPcreUnicodeSupport;

        if ($hasPcreUnicodeSupport === null) {
            $hasPcreUnicodeSupport = defined('PREG_BAD_UTF8_OFFSET_ERROR') && @preg_match('/\pL/u', 'a') == 1;
        }

        return $hasPcreUnicodeSupport;
    }

    /**
     * Get the available locales for Kendo scripts
     *
     * @return array
     */
    public static function getLocales()
    {
        return array(
            'af' => array(
                'name' => 'Afrikaans',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'af-ZA' => array(
                'name' => 'Afrikaans (South Africa)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'sq' => array(
                'name' => 'Albanian',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'sq-AL' => array(
                'name' => 'Albanian (Albania)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'gsw' => array(
                'name' => 'Alsatian',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gsw-FR' => array(
                'name' => 'Alsatian (France)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'am' => array(
                'name' => 'Amharic',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'am-ET' => array(
                'name' => 'Amharic (Ethiopia)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ar' => array(
                'name' => 'Arabic',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/y h:i A'
            ),
            'ar-DZ' => array(
                'name' => 'Arabic (Algeria)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ar-BH' => array(
                'name' => 'Arabic (Bahrain)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-EG' => array(
                'name' => 'Arabic (Egypt)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-IQ' => array(
                'name' => 'Arabic (Iraq)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-JO' => array(
                'name' => 'Arabic (Jordan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-KW' => array(
                'name' => 'Arabic (Kuwait)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-LB' => array(
                'name' => 'Arabic (Lebanon)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-LY' => array(
                'name' => 'Arabic (Libya)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-MA' => array(
                'name' => 'Arabic (Morocco)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ar-OM' => array(
                'name' => 'Arabic (Oman)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-QA' => array(
                'name' => 'Arabic (Qatar)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-SA' => array(
                'name' => 'Arabic (Saudi Arabia)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/y h:i A'
            ),
            'ar-SY' => array(
                'name' => 'Arabic (Syria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-TN' => array(
                'name' => 'Arabic (Tunisia)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ar-AE' => array(
                'name' => 'Arabic (U.A.E.)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'ar-YE' => array(
                'name' => 'Arabic (Yemen)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'hy' => array(
                'name' => 'Armenian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'hy-AM' => array(
                'name' => 'Armenian (Armenia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'as' => array(
                'name' => 'Assamese',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'a g:i',
                'dateTimeFormat' => 'd-m-Y a g:i'
            ),
            'as-IN' => array(
                'name' => 'Assamese (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'a g:i',
                'dateTimeFormat' => 'd-m-Y a g:i'
            ),
            'az' => array(
                'name' => 'Azeri',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'az-Cyrl' => array(
                'name' => 'Azeri (Cyrillic)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'az-Cyrl-AZ' => array(
                'name' => 'Azeri (Cyrillic, Azerbaijan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'az-Latn' => array(
                'name' => 'Azeri (Latin)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'az-Latn-AZ' => array(
                'name' => 'Azeri (Latin, Azerbaijan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ba' => array(
                'name' => 'Bashkir',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.y G:i'
            ),
            'ba-RU' => array(
                'name' => 'Bashkir (Russia)',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.y G:i'
            ),
            'eu' => array(
                'name' => 'Basque',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/m/d G:i'
            ),
            'eu-ES' => array(
                'name' => 'Basque (Spain)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'be' => array(
                'name' => 'Belarusian',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y H:i'
            ),
            'be-BY' => array(
                'name' => 'Belarusian (Belarus)',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y H:i'
            ),
            'bn' => array(
                'name' => 'Bengali',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-y H.i'
            ),
            'bn-BD' => array(
                'name' => 'Bengali (Bangladesh)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-y H.i'
            ),
            'bn-IN' => array(
                'name' => 'Bengali (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-y H.i'
            ),
            'bs' => array(
                'name' => 'Bosnian',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'bs-Cyrl' => array(
                'name' => 'Bosnian (Cyrillic)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'bs-Cyrl-BA' => array(
                'name' => 'Bosnian (Cyrillic, Bosnia and Herzegovina)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'bs-Latn' => array(
                'name' => 'Bosnian (Latin)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'bs-Latn-BA' => array(
                'name' => 'Bosnian (Latin, Bosnia and Herzegovina)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'br' => array(
                'name' => 'Breton',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'br-FR' => array(
                'name' => 'Breton (France)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'bg' => array(
                'name' => 'Bulgarian',
                'dateFormat' => 'j.n.Y г.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y г. G:i'
            ),
            'bg-BG' => array(
                'name' => 'Bulgarian (Bulgaria)',
                'dateFormat' => 'j.n.Y г.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y г. G:i'
            ),
            'my' => array(
                'name' => 'Burmese',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'my-MM' => array(
                'name' => 'Burmese (Myanmar)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'ca' => array(
                'name' => 'Catalan',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'ca-ES' => array(
                'name' => 'Catalan (Spain)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'tzm' => array(
                'name' => 'Central Atlas Tamazight',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'tzm-Latn' => array(
                'name' => 'Central Atlas Tamazight (Latin)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'tzm-Latn-DZ' => array(
                'name' => 'Central Atlas Tamazight (Latin, Algeria)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'tzm-Tfng' => array(
                'name' => 'Central Atlas Tamazight (Tifinagh)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'tzm-Tfng-MA' => array(
                'name' => 'Central Atlas Tamazight (Tifinagh, Morocco)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'ku' => array(
                'name' => 'Central Kurdish',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'ku-Arab' => array(
                'name' => 'Central Kurdish (Arabic)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'ku-Arab-IQ' => array(
                'name' => 'Central Kurdish (Arabic, Iraq)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'chr' => array(
                'name' => 'Cherokee',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'chr-Cher' => array(
                'name' => 'Cherokee',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'chr-Cher-US' => array(
                'name' => 'Cherokee (United States)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'zh' => array(
                'name' => 'Chinese (Simplified)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'zh-CHS' => array(
                'name' => 'Chinese (Simplified) (zh-CHS)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'zh-Hans' => array(
                'name' => 'Chinese (Simplified) (zh-Hans)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'zh-CN' => array(
                'name' => 'Chinese (Simplified, People\'s Republic of China)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'zh-SG' => array(
                'name' => 'Chinese (Simplified, Singapore)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'j/n/Y A g:i'
            ),
            'zh-CHT' => array(
                'name' => 'Chinese (Traditional) (zh-CHT)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'zh-Hant' => array(
                'name' => 'Chinese (Traditional) (zh-Hant)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'zh-HK' => array(
                'name' => 'Chinese (Traditional, Hong Kong S.A.R.)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'zh-MO' => array(
                'name' => 'Chinese (Traditional, Macao S.A.R.)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'zh-TW' => array(
                'name' => 'Chinese (Traditional, Taiwan)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'Y/n/j A h:i'
            ),
            'co' => array(
                'name' => 'Corsican',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'co-FR' => array(
                'name' => 'Corsican (France)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'hr' => array(
                'name' => 'Croatian',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'hr-BA' => array(
                'name' => 'Croatian (Latin, Bosnia and Herzegovina)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'hr-HR' => array(
                'name' => 'Croatian (Croatia)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'cs' => array(
                'name' => 'Czech',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j. n. Y G:i'
            ),
            'cs-CZ' => array(
                'name' => 'Czech (Czech Republic)',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j. n. Y G:i'
            ),
            'da' => array(
                'name' => 'Danish',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'da-DK' => array(
                'name' => 'Danish (Denmark)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'prs' => array(
                'name' => 'Dari',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y/n/j g:i A'
            ),
            'prs-AF' => array(
                'name' => 'Dari (Afghanistan)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y/n/j g:i A'
            ),
            'dv' => array(
                'name' => 'Divehi',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/y H:i'
            ),
            'dv-MV' => array(
                'name' => 'Divehi (Maldives)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/y H:i'
            ),
            'nl' => array(
                'name' => 'Dutch',
                'dateFormat' => 'j-n-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-n-Y H:i'
            ),
            'nl-BE' => array(
                'name' => 'Dutch (Belgium)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/m/Y G:i'
            ),
            'nl-NL' => array(
                'name' => 'Dutch (Netherlands)',
                'dateFormat' => 'j-n-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-n-Y H:i'
            ),
            'en' => array(
                'name' => 'English',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-AU' => array(
                'name' => 'English (Australia)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'en-BZ' => array(
                'name' => 'English (Belize)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'en-CA' => array(
                'name' => 'English (Canada)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y-m-d g:i A'
            ),
            'en-029' => array(
                'name' => 'English (Caribbean)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-HK' => array(
                'name' => 'English (Hong Kong)',
                'dateFormat' => 'j/n/y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/y g:i A'
            ),
            'en-IN' => array(
                'name' => 'English (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'en-IE' => array(
                'name' => 'English (Ireland)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-JM' => array(
                'name' => 'English (Jamaica)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-MY' => array(
                'name' => 'English (Malaysia)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'en-NZ' => array(
                'name' => 'English (New Zealand)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/m/Y g:i a'
            ),
            'en-PH' => array(
                'name' => 'English (Republic of the Philippines)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-SG' => array(
                'name' => 'English (Singapore)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'en-ZA' => array(
                'name' => 'English (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y-m-d h:i A'
            ),
            'en-TT' => array(
                'name' => 'English (Trinidad and Tobago)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'en-GB' => array(
                'name' => 'English (United Kingdom)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'en-US' => array(
                'name' => 'English (United States)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'en-ZW' => array(
                'name' => 'English (Zimbabwe)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'et' => array(
                'name' => 'Estonian',
                'dateFormat' => 'j.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.m.Y G:i'
            ),
            'et-EE' => array(
                'name' => 'Estonian (Estonia)',
                'dateFormat' => 'j.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.m.Y G:i'
            ),
            'fo' => array(
                'name' => 'Faroese',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'fo-FO' => array(
                'name' => 'Faroese (Faroe Islands)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'fil' => array(
                'name' => 'Filipino',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'fil-PH' => array(
                'name' => 'Filipino (Philippines)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'fi' => array(
                'name' => 'Finnish',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'fi-FI' => array(
                'name' => 'Finnish (Finland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'fr' => array(
                'name' => 'French',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-BE' => array(
                'name' => 'French (Belgium)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'fr-CI' => array(
                'name' => 'French (Côte d’Ivoire)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-CM' => array(
                'name' => 'French (Cameroon)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-CA' => array(
                'name' => 'French (Canada)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'fr-CD' => array(
                'name' => 'French (Congo, DRC)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-FR' => array(
                'name' => 'French (France)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-HT' => array(
                'name' => 'French (Haiti)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-LU' => array(
                'name' => 'French (Luxembourg)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-ML' => array(
                'name' => 'French (Mali)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-MA' => array(
                'name' => 'French (Morocco)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-MC' => array(
                'name' => 'French (Principality of Monaco)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-RE' => array(
                'name' => 'French (Réunion)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-SN' => array(
                'name' => 'French (Senegal)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'fr-CH' => array(
                'name' => 'French (Switzerland)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'fy' => array(
                'name' => 'Frisian',
                'dateFormat' => 'j-n-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j-n-Y G:i'
            ),
            'fy-NL' => array(
                'name' => 'Frisian (Netherlands)',
                'dateFormat' => 'j-n-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j-n-Y G:i'
            ),
            'ff' => array(
                'name' => 'Fulah',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ff-Latn' => array(
                'name' => 'Fulah (Latin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ff-Latn-SN' => array(
                'name' => 'Fulah (Latin, Senegal)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gl' => array(
                'name' => 'Galician',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'gl-ES' => array(
                'name' => 'Galician (Spain)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'ka' => array(
                'name' => 'Georgian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ka-GE' => array(
                'name' => 'Georgian (Georgia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'de' => array(
                'name' => 'German',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-AT' => array(
                'name' => 'German (Austria)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-DE' => array(
                'name' => 'German (Germany)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-LI' => array(
                'name' => 'German (Liechtenstein)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-LU' => array(
                'name' => 'German (Luxembourg)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'de-CH' => array(
                'name' => 'German (Switzerland)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'el' => array(
                'name' => 'Greek',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/Y g:i a'
            ),
            'el-GR' => array(
                'name' => 'Greek (Greece)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/Y g:i a'
            ),
            'kl' => array(
                'name' => 'Greenlandic',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'kl-GL' => array(
                'name' => 'Greenlandic (Greenland)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'gn' => array(
                'name' => 'Guarani',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gn-PY' => array(
                'name' => 'Guarani (Paraguay)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gu' => array(
                'name' => 'Gujarati',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'gu-IN' => array(
                'name' => 'Gujarati (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'ha' => array(
                'name' => 'Hausa',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ha-Latn' => array(
                'name' => 'Hausa (Latin)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ha-Latn-NG' => array(
                'name' => 'Hausa (Latin, Nigeria)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'haw' => array(
                'name' => 'Hawaiian',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'haw-US' => array(
                'name' => 'Hawaiian (United States)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'he' => array(
                'name' => 'Hebrew',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'he-IL' => array(
                'name' => 'Hebrew (Israel)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'hi' => array(
                'name' => 'Hindi',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'hi-IN' => array(
                'name' => 'Hindi (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'hu' => array(
                'name' => 'Hungarian',
                'dateFormat' => 'Y.m.d.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y.m.d. G:i'
            ),
            'hu-HU' => array(
                'name' => 'Hungarian (Hungary)',
                'dateFormat' => 'Y.m.d.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y.m.d. G:i'
            ),
            'is' => array(
                'name' => 'Icelandic',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'is-IS' => array(
                'name' => 'Icelandic (Iceland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'ig' => array(
                'name' => 'Igbo',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g.i A',
                'dateTimeFormat' => 'j/n/Y g.i A'
            ),
            'ig-NG' => array(
                'name' => 'Igbo (Nigeria)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g.i A',
                'dateTimeFormat' => 'j/n/Y g.i A'
            ),
            'id' => array(
                'name' => 'Indonesian',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'id-ID' => array(
                'name' => 'Indonesian (Indonesia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'iu' => array(
                'name' => 'Inuktitut',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'iu-Latn' => array(
                'name' => 'Inuktitut (Latin)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'iu-Latn-CA' => array(
                'name' => 'Inuktitut (Latin, Canada)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/m/Y g:i A'
            ),
            'iu-Cans' => array(
                'name' => 'Inuktitut (Syllabics)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'iu-Cans-CA' => array(
                'name' => 'Inuktitut (Syllabics, Canada)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ga' => array(
                'name' => 'Irish',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'ga-IE' => array(
                'name' => 'Irish (Ireland)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'it' => array(
                'name' => 'Italian',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'it-IT' => array(
                'name' => 'Italian (Italy)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'it-CH' => array(
                'name' => 'Italian (Switzerland)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ja' => array(
                'name' => 'Japanese',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/m/d G:i'
            ),
            'ja-JP' => array(
                'name' => 'Japanese (Japan)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/m/d G:i'
            ),
            'jv' => array(
                'name' => 'Javanese',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'jv-Latn' => array(
                'name' => 'Javanese (Latin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'jv-Latn-ID' => array(
                'name' => 'Javanese (Latin, Indonesia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd/m/Y H.i'
            ),
            'kn' => array(
                'name' => 'Kannada',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'kn-IN' => array(
                'name' => 'Kannada (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'kk' => array(
                'name' => 'Kazakh',
                'dateFormat' => 'j-n-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-n-y H:i'
            ),
            'kk-KZ' => array(
                'name' => 'Kazakh (Kazakhstan)',
                'dateFormat' => 'j-n-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-n-y H:i'
            ),
            'km' => array(
                'name' => 'Khmer',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/y G:i'
            ),
            'km-KH' => array(
                'name' => 'Khmer (Cambodia)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/y G:i'
            ),
            'qut' => array(
                'name' => 'K\'iche (qut)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'qut-GT' => array(
                'name' => 'K\'iche (Guatemala)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'rw' => array(
                'name' => 'Kinyarwanda',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/m/Y G:i'
            ),
            'rw-RW' => array(
                'name' => 'Kinyarwanda (Rwanda)',
                'dateFormat' => 'j/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/m/Y G:i'
            ),
            'sw' => array(
                'name' => 'Kiswahili',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'sw-KE' => array(
                'name' => 'Kiswahili (Kenya)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'kok' => array(
                'name' => 'Konkani',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'kok-IN' => array(
                'name' => 'Konkani (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'ko' => array(
                'name' => 'Korean',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y-m-d A g:i'
            ),
            'ko-KR' => array(
                'name' => 'Korean (Korea)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y-m-d A g:i'
            ),
            'ky' => array(
                'name' => 'Kyrgyz',
                'dateFormat' => 'j-n-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-n-y H:i'
            ),
            'ky-KG' => array(
                'name' => 'Kyrgyz (Kyrgyzstan)',
                'dateFormat' => 'j-n-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j-n-y H:i'
            ),
            'lo' => array(
                'name' => 'Lao',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'lo-LA' => array(
                'name' => 'Lao (Lao P.D.R.)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'lv' => array(
                'name' => 'Latvian',
                'dateFormat' => 'd.m.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y. G:i'
            ),
            'lv-LV' => array(
                'name' => 'Latvian (Latvia)',
                'dateFormat' => 'd.m.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y. G:i'
            ),
            'lt' => array(
                'name' => 'Lithuanian',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'lt-LT' => array(
                'name' => 'Lithuanian (Lithuania)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'dsb' => array(
                'name' => 'Lower Sorbian',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j. n. Y H:i'
            ),
            'dsb-DE' => array(
                'name' => 'Lower Sorbian (Germany)',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j. n. Y H:i'
            ),
            'lb' => array(
                'name' => 'Luxembourgish',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y H:i'
            ),
            'lb-LU' => array(
                'name' => 'Luxembourgish (Luxembourg)',
                'dateFormat' => 'd.m.y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y H:i'
            ),
            'mk' => array(
                'name' => 'Macedonian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'mk-MK' => array(
                'name' => 'Macedonian (Former Yugoslav Republic of Macedonia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'mg' => array(
                'name' => 'Malagasy',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'mg-MG' => array(
                'name' => 'Malagasy (Madagascar)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j/n/Y H:i'
            ),
            'ms' => array(
                'name' => 'Malay',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'ms-BN' => array(
                'name' => 'Malay (Brunei Darussalam)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'ms-MY' => array(
                'name' => 'Malay (Malaysia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'ml' => array(
                'name' => 'Malayalam',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-y H.i'
            ),
            'ml-IN' => array(
                'name' => 'Malayalam (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-y H.i'
            ),
            'mt' => array(
                'name' => 'Maltese',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mt-MT' => array(
                'name' => 'Maltese (Malta)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'mi' => array(
                'name' => 'Maori',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'mi-NZ' => array(
                'name' => 'Maori (New Zealand)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'arn' => array(
                'name' => 'Mapudungun',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'arn-CL' => array(
                'name' => 'Mapudungun (Chile)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'mr' => array(
                'name' => 'Marathi',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'mr-IN' => array(
                'name' => 'Marathi (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'moh' => array(
                'name' => 'Mohawk',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'moh-CA' => array(
                'name' => 'Mohawk (Canada)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'mn' => array(
                'name' => 'Mongolian',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'mn-Cyrl' => array(
                'name' => 'Mongolian (Cyrillic)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'mn-MN' => array(
                'name' => 'Mongolian (Cyrillic, Mongolia)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'mn-Mong' => array(
                'name' => 'Mongolian (Traditional Mongolian)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'mn-Mong-MN' => array(
                'name' => 'Mongolian (Traditional Mongolian, Mongolia)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'mn-Mong-CN' => array(
                'name' => 'Mongolian (Traditional Mongolian, People\'s Republic of China)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y/n/j G:i'
            ),
            'nqo' => array(
                'name' => 'N\'ko',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'd/m/Y A h:i'
            ),
            'nqo-GN' => array(
                'name' => 'N\'ko (Guinea)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'd/m/Y A h:i'
            ),
            'ne' => array(
                'name' => 'Nepali',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'ne-IN' => array(
                'name' => 'Nepali (India)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'ne-NP' => array(
                'name' => 'Nepali (Nepal)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'no' => array(
                'name' => 'Norwegian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'nb' => array(
                'name' => 'Norwegian (Bokmål)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'nb-NO' => array(
                'name' => 'Norwegian (Bokmål, Norway)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'nn' => array(
                'name' => 'Norwegian (Nynorsk)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'nn-NO' => array(
                'name' => 'Norwegian (Nynorsk, Norway)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'oc' => array(
                'name' => 'Occitan',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H \h i',
                'dateTimeFormat' => 'd/m/Y H \h i'
            ),
            'oc-FR' => array(
                'name' => 'Occitan (France)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H \h i',
                'dateTimeFormat' => 'd/m/Y H \h i'
            ),
            'or' => array(
                'name' => 'Odia',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'or-IN' => array(
                'name' => 'Odia (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'om' => array(
                'name' => 'Oromo',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/y g:i A'
            ),
            'om-ET' => array(
                'name' => 'Oromo (Ethiopia)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/y g:i A'
            ),
            'ps' => array(
                'name' => 'Pashto',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y/n/j g:i A'
            ),
            'ps-AF' => array(
                'name' => 'Pashto (Afghanistan)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'Y/n/j g:i A'
            ),
            'fa' => array(
                'name' => 'Persian',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'fa-IR' => array(
                'name' => 'Persian (Iran)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'pl' => array(
                'name' => 'Polish',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'pl-PL' => array(
                'name' => 'Polish (Poland)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'pt' => array(
                'name' => 'Portuguese',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'pt-AO' => array(
                'name' => 'Portuguese (Angola)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'pt-BR' => array(
                'name' => 'Portuguese (Brazil)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'pt-PT' => array(
                'name' => 'Portuguese (Portugal)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'pa' => array(
                'name' => 'Punjabi',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'd-m-y A h:i'
            ),
            'pa-Arab' => array(
                'name' => 'Punjabi (Arabic)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'g.i A',
                'dateTimeFormat' => 'd-m-y g.i A'
            ),
            'pa-Arab-PK' => array(
                'name' => 'Punjabi (Arabic, Islamic Republic of Pakistan)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'g.i A',
                'dateTimeFormat' => 'd-m-y g.i A'
            ),
            'pa-IN' => array(
                'name' => 'Punjabi (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'A h:i',
                'dateTimeFormat' => 'd-m-y A h:i'
            ),
            'quz' => array(
                'name' => 'Quechua',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'quz-BO' => array(
                'name' => 'Quechua (Bolivia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'quz-EC' => array(
                'name' => 'Quechua (Ecuador)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'quz-PE' => array(
                'name' => 'Quechua (Peru)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'ro' => array(
                'name' => 'Romanian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ro-MD' => array(
                'name' => 'Romanian (Moldova)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ro-RO' => array(
                'name' => 'Romanian (Romania)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'rm' => array(
                'name' => 'Romansh',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'rm-CH' => array(
                'name' => 'Romansh (Switzerland)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'ru' => array(
                'name' => 'Russian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ru-RU' => array(
                'name' => 'Russian (Russia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'ru-UA' => array(
                'name' => 'Russian (Ukraine)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'sah' => array(
                'name' => 'Sakha',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'sah-RU' => array(
                'name' => 'Sakha (Russia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'smn' => array(
                'name' => 'Sami, Inari',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'smn-FI' => array(
                'name' => 'Sami, Inari (Finland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'smj' => array(
                'name' => 'Sami, Lule',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'smj-NO' => array(
                'name' => 'Sami, Lule (Norway)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'smj-SE' => array(
                'name' => 'Sami, Lule (Sweden)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'se' => array(
                'name' => 'Sami, Northern',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'se-FI' => array(
                'name' => 'Sami, Northern (Finland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'se-NO' => array(
                'name' => 'Sami, Northern (Norway)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'se-SE' => array(
                'name' => 'Sami, Northern (Sweden)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sms' => array(
                'name' => 'Sami, Skolt',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'sms-FI' => array(
                'name' => 'Sami, Skolt (Finland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'sma' => array(
                'name' => 'Sami, Southern',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sma-NO' => array(
                'name' => 'Sami, Southern (Norway)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'sma-SE' => array(
                'name' => 'Sami, Southern (Sweden)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sa' => array(
                'name' => 'Sanskrit',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'sa-IN' => array(
                'name' => 'Sanskrit (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'gd' => array(
                'name' => 'Scottish Gaelic',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'gd-GB' => array(
                'name' => 'Scottish Gaelic (United Kingdom)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'sr' => array(
                'name' => 'Serbian',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Cyrl' => array(
                'name' => 'Serbian (Cyrillic)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Cyrl-BA' => array(
                'name' => 'Serbian (Cyrillic, Bosnia and Herzegovina)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Cyrl-CS' => array(
                'name' => 'Serbian (Cyrillic, Serbia and Montenegro (Former))',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Cyrl-ME' => array(
                'name' => 'Serbian (Cyrillic, Montenegro)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Cyrl-RS' => array(
                'name' => 'Serbian (Cyrillic, Serbia)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Latn' => array(
                'name' => 'Serbian (Latin)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Latn-BA' => array(
                'name' => 'Serbian (Latin, Bosnia and Herzegovina)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Latn-CS' => array(
                'name' => 'Serbian (Latin, Serbia and Montenegro (Former))',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Latn-ME' => array(
                'name' => 'Serbian (Latin, Montenegro)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'sr-Latn-RS' => array(
                'name' => 'Serbian (Latin, Serbia)',
                'dateFormat' => 'j.n.Y.',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y. G:i'
            ),
            'nso' => array(
                'name' => 'Sesotho sa Leboa',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/y h:i A'
            ),
            'nso-ZA' => array(
                'name' => 'Sesotho sa Leboa (South Africa)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/y h:i A'
            ),
            'tn' => array(
                'name' => 'Setswana',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/y h:i A'
            ),
            'tn-BW' => array(
                'name' => 'Setswana (Botswana)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/y h:i A'
            ),
            'tn-ZA' => array(
                'name' => 'Setswana (South Africa)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/y h:i A'
            ),
            'sn' => array(
                'name' => 'Shona',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sn-Latn' => array(
                'name' => 'Shona (Latin)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sn-Latn-ZW' => array(
                'name' => 'Shona (Latin, Zimbabwe)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sd' => array(
                'name' => 'Sindhi',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sd-Arab' => array(
                'name' => 'Sindhi (Arabic)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'sd-Arab-PK' => array(
                'name' => 'Sindhi (Arabic, Islamic Republic of Pakistan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'si' => array(
                'name' => 'Sinhala',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y-m-d A g:i'
            ),
            'si-LK' => array(
                'name' => 'Sinhala (Sri Lanka)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y-m-d A g:i'
            ),
            'sk' => array(
                'name' => 'Slovak',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'sk-SK' => array(
                'name' => 'Slovak (Slovakia)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'sl' => array(
                'name' => 'Slovenian',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'sl-SI' => array(
                'name' => 'Slovenian (Slovenia)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j.n.Y G:i'
            ),
            'so' => array(
                'name' => 'Somali',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/y g:i a'
            ),
            'so-SO' => array(
                'name' => 'Somali (Somalia)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/y g:i a'
            ),
            'st' => array(
                'name' => 'Sotho',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'st-ZA' => array(
                'name' => 'Sotho (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'es' => array(
                'name' => 'Spanish',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'es-AR' => array(
                'name' => 'Spanish (Argentina)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-VE' => array(
                'name' => 'Spanish (Bolivarian Republic of Venezuela)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd-m-Y h:i a'
            ),
            'es-BO' => array(
                'name' => 'Spanish (Bolivia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-CL' => array(
                'name' => 'Spanish (Chile)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd-m-Y G:i'
            ),
            'es-CO' => array(
                'name' => 'Spanish (Colombia)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'es-CR' => array(
                'name' => 'Spanish (Costa Rica)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-DO' => array(
                'name' => 'Spanish (Dominican Republic)',
                'dateFormat' => 'j/n/y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/y g:i a'
            ),
            'es-EC' => array(
                'name' => 'Spanish (Ecuador)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'es-SV' => array(
                'name' => 'Spanish (El Salvador)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-GT' => array(
                'name' => 'Spanish (Guatemala)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'd/m/Y g:i a'
            ),
            'es-HN' => array(
                'name' => 'Spanish (Honduras)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-419' => array(
                'name' => 'Spanish (Latin America)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/y H:i'
            ),
            'es-MX' => array(
                'name' => 'Spanish (Mexico)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-NI' => array(
                'name' => 'Spanish (Nicaragua)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-PA' => array(
                'name' => 'Spanish (Panama)',
                'dateFormat' => 'j/n/y',
                'timeFormat' => 'g:i a',
                'dateTimeFormat' => 'j/n/y g:i a'
            ),
            'es-PY' => array(
                'name' => 'Spanish (Paraguay)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-PE' => array(
                'name' => 'Spanish (Peru)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-PR' => array(
                'name' => 'Spanish (Puerto Rico)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i a',
                'dateTimeFormat' => 'd/m/Y h:i a'
            ),
            'es-ES' => array(
                'name' => 'Spanish (Spain)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'es-US' => array(
                'name' => 'Spanish (United States)',
                'dateFormat' => 'n/j/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'n/j/Y g:i A'
            ),
            'es-UY' => array(
                'name' => 'Spanish (Uruguay)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd/m/Y G:i'
            ),
            'zgh' => array(
                'name' => 'Standard Morrocan Tamazight',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-Y H.i'
            ),
            'zgh-Tfng' => array(
                'name' => 'Standard Morrocan Tamazight (Tifinagh)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-Y H.i'
            ),
            'zgh-Tfng-MA' => array(
                'name' => 'Standard Morrocan Tamazight (Tifinagh, Morocco)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H.i',
                'dateTimeFormat' => 'd-m-Y H.i'
            ),
            'sv' => array(
                'name' => 'Swedish',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'sv-FI' => array(
                'name' => 'Swedish (Finland)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'sv-SE' => array(
                'name' => 'Swedish (Sweden)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'syr' => array(
                'name' => 'Syriac',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'syr-SY' => array(
                'name' => 'Syriac (Syria)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd/m/Y h:i A'
            ),
            'tg' => array(
                'name' => 'Tajik',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'tg-Cyrl' => array(
                'name' => 'Tajik (Cyrillic)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'tg-Cyrl-TJ' => array(
                'name' => 'Tajik (Cyrillic, Tajikistan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ta' => array(
                'name' => 'Tamil',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'ta-IN' => array(
                'name' => 'Tamil (India)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'ta-LK' => array(
                'name' => 'Tamil (Sri Lanka)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-Y H:i'
            ),
            'tt' => array(
                'name' => 'Tatar',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'tt-RU' => array(
                'name' => 'Tatar (Russia)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'te' => array(
                'name' => 'Telugu',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'te-IN' => array(
                'name' => 'Telugu (India)',
                'dateFormat' => 'd-m-y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd-m-y H:i'
            ),
            'th' => array(
                'name' => 'Thai',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'th-TH' => array(
                'name' => 'Thai (Thailand)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'j/n/Y G:i'
            ),
            'bo' => array(
                'name' => 'Tibetan',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/n/j H:i'
            ),
            'bo-CN' => array(
                'name' => 'Tibetan (People\'s Republic of China)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y/n/j H:i'
            ),
            'ti' => array(
                'name' => 'Tigrinya',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ti-ER' => array(
                'name' => 'Tigrinya (Eritrea)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ti-ET' => array(
                'name' => 'Tigrinya (Ethiopia)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'ts' => array(
                'name' => 'Tsonga',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'ts-ZA' => array(
                'name' => 'Tsonga (South Africa)',
                'dateFormat' => 'Y-m-d',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'Y-m-d H:i'
            ),
            'tr' => array(
                'name' => 'Turkish',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'tr-TR' => array(
                'name' => 'Turkish (Turkey)',
                'dateFormat' => 'j.n.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'j.n.Y H:i'
            ),
            'tk' => array(
                'name' => 'Turkmen',
                'dateFormat' => 'd.m.y ý.',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y ý. H:i'
            ),
            'tk-TM' => array(
                'name' => 'Turkmen (Turkmenistan)',
                'dateFormat' => 'd.m.y ý.',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.y ý. H:i'
            ),
            'uk' => array(
                'name' => 'Ukrainian',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'uk-UA' => array(
                'name' => 'Ukrainian (Ukraine)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'd.m.Y G:i'
            ),
            'hsb' => array(
                'name' => 'Upper Sorbian',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'G.i',
                'dateTimeFormat' => 'j. n. Y G.i'
            ),
            'hsb-DE' => array(
                'name' => 'Upper Sorbian (Germany)',
                'dateFormat' => 'j. n. Y',
                'timeFormat' => 'G.i',
                'dateTimeFormat' => 'j. n. Y G.i'
            ),
            'ur' => array(
                'name' => 'Urdu',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ur-IN' => array(
                'name' => 'Urdu (India)',
                'dateFormat' => 'j/n/y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/y g:i A'
            ),
            'ur-PK' => array(
                'name' => 'Urdu (Islamic Republic of Pakistan)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'ug' => array(
                'name' => 'Uyghur',
                'dateFormat' => 'Y-n-j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y-n-j G:i'
            ),
            'ug-CN' => array(
                'name' => 'Uyghur (People\'s Republic of China)',
                'dateFormat' => 'Y-n-j',
                'timeFormat' => 'G:i',
                'dateTimeFormat' => 'Y-n-j G:i'
            ),
            'uz' => array(
                'name' => 'Uzbek',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'uz-Cyrl' => array(
                'name' => 'Uzbek (Cyrillic)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'uz-Cyrl-UZ' => array(
                'name' => 'Uzbek (Cyrillic, Uzbekistan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'uz-Latn' => array(
                'name' => 'Uzbek (Latin)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'uz-Latn-UZ' => array(
                'name' => 'Uzbek (Latin, Uzbekistan)',
                'dateFormat' => 'd.m.Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd.m.Y H:i'
            ),
            'ca-ES-valencia' => array(
                'name' => 'Valencian (Spain)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/y H:i'
            ),
            'vi' => array(
                'name' => 'Vietnamese',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'vi-VN' => array(
                'name' => 'Vietnamese (Vietnam)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'd/m/Y g:i A'
            ),
            'cy' => array(
                'name' => 'Welsh',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/y H:i'
            ),
            'cy-GB' => array(
                'name' => 'Welsh (United Kingdom)',
                'dateFormat' => 'd/m/y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/y H:i'
            ),
            'wo' => array(
                'name' => 'Wolof',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'wo-SN' => array(
                'name' => 'Wolof (Senegal)',
                'dateFormat' => 'd/m/Y',
                'timeFormat' => 'H:i',
                'dateTimeFormat' => 'd/m/Y H:i'
            ),
            'xh' => array(
                'name' => 'Xhosa',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'xh-ZA' => array(
                'name' => 'Xhosa (South Africa)',
                'dateFormat' => 'Y/m/d',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'Y/m/d h:i A'
            ),
            'ii' => array(
                'name' => 'Yi',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y/n/j A g:i'
            ),
            'ii-CN' => array(
                'name' => 'Yi (People\'s Republic of China)',
                'dateFormat' => 'Y/n/j',
                'timeFormat' => 'A g:i',
                'dateTimeFormat' => 'Y/n/j A g:i'
            ),
            'yo' => array(
                'name' => 'Yoruba',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'yo-NG' => array(
                'name' => 'Yoruba (Nigeria)',
                'dateFormat' => 'j/n/Y',
                'timeFormat' => 'g:i A',
                'dateTimeFormat' => 'j/n/Y g:i A'
            ),
            'zu' => array(
                'name' => 'Zulu',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd-m-Y h:i A'
            ),
            'zu-ZA' => array(
                'name' => 'Zulu (South Africa)',
                'dateFormat' => 'd-m-Y',
                'timeFormat' => 'h:i A',
                'dateTimeFormat' => 'd-m-Y h:i A'
            )
        );
    }

    /**
     * Get the locale data with the given locale code
     *
     * If the locale does not exist, the default en-US locale will be returned
     *
     * @param   string  $locale
     * @return  array
     */
    public static function getLocale($locale = '')
    {
        $locales = Quform::getLocales();

        if ( ! empty($locales[$locale])) {
            return $locales[$locale];
        }

        return $locales['en-US'];
    }

    /**
     * Get the plugin icon SVG in the given color
     *
     * @param   string  $color
     * @return  string
     */
    public static function getPluginIcon($color = '')
    {
        $icon = '<?xml version="1.0" standalone="no"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 20010904//EN"
 "http://www.w3.org/TR/2001/REC-SVG-20010904/DTD/svg10.dtd">
<svg version="1.0" xmlns="http://www.w3.org/2000/svg"
 width="397.000000pt" height="354.000000pt" viewBox="0 0 397.000000 354.000000"
 preserveAspectRatio="xMidYMid meet">

<g transform="translate(0.000000,354.000000) scale(0.100000,-0.100000)"
fill="#82878c" stroke="none">
<path d="M1660 3530 c-548 -67 -1036 -347 -1337 -768 -146 -204 -244 -433
-295 -687 -32 -160 -32 -451 0 -614 157 -784 810 -1360 1644 -1450 136 -15
2208 -15 2241 0 53 24 57 47 57 304 0 257 -4 280 -57 304 -16 7 -128 11 -319
11 l-295 0 67 83 c226 277 344 569 376 929 19 224 -6 432 -82 659 -206 622
-766 1089 -1450 1210 -131 24 -428 33 -550 19z m400 -635 c135 -21 230 -49
346 -104 139 -67 244 -140 344 -240 451 -454 449 -1114 -5 -1566 -467 -465
-1243 -473 -1726 -18 -148 140 -275 352 -326 548 -22 87 -26 120 -26 255 0
136 4 168 27 255 46 174 144 355 268 490 272 297 692 443 1098 380z"/>
<path d="M1255 2341 c-11 -5 -31 -21 -45 -36 -22 -23 -25 -36 -25 -96 0 -64 2
-71 33 -101 l32 -33 660 0 660 0 32 33 c31 30 33 37 33 102 0 65 -2 72 -33
102 l-32 33 -648 2 c-356 1 -656 -2 -667 -6z"/>
<path d="M1255 1901 c-11 -5 -31 -21 -45 -36 -22 -23 -25 -36 -25 -96 0 -64 2
-71 33 -101 l32 -33 405 0 405 0 32 33 c31 30 33 37 33 102 0 65 -2 72 -33
102 l-32 33 -393 2 c-215 1 -401 -2 -412 -6z"/>
<path d="M1255 1461 c-11 -5 -31 -21 -45 -36 -22 -23 -25 -36 -25 -96 0 -64 2
-71 33 -101 l32 -33 165 0 165 0 32 33 c31 30 33 37 33 102 0 65 -2 72 -33
102 l-32 33 -153 2 c-83 1 -161 -1 -172 -6z"/>
</g>
</svg>';

        if (Quform::isNonEmptyString($color)) {
            $icon = str_replace('fill="#82878c"', sprintf('fill="%s"', $color), $icon);
        }

        return 'data:image/svg+xml;base64,' . base64_encode($icon);
    }

    /**
     * Does the current user have any of the given capabilities?
     *
     * @param   array|string  $caps
     * @return  bool
     */
    public static function currentUserCan($caps)
    {
        if ( ! is_user_logged_in()) {
            return false;
        }

        if (current_user_can('quform_full_access')) {
            return true;
        }

        if ( ! is_array($caps)) {
            $caps = array($caps);
        }

        foreach ($caps as $cap) {
            if (current_user_can($cap)) {
                return true;
            }
        }

        return false;
    }

    /**
     * If the value is numeric it will append 'px' otherwise return the value unchanged
     *
     * @param   string  $value
     * @return  string
     */
    public static function addCssUnit($value)
    {
        if (is_numeric($value)) {
            $value = sprintf('%spx', $value);
        }

        return $value;
    }

    /**
     * Format the given count into thousands if necessary e.g. 1100 becomes 1.1k
     *
     * @param   int     $count
     * @return  string
     */
    public static function formatCount($count)
    {
        if ($count >= 1000000000) {
            $count = floor($count / 100000000) * 100000000;
            $precision = $count % 1000000000 < 100000000 ? 0 : 1;

            return sprintf(_x('%sb', 'number ending in b (billions)', 'quform'), number_format_i18n($count / 1000000000, $precision));
        } else if ($count >= 1000000) {
            $count = floor($count / 100000) * 100000;
            $precision = $count % 1000000 < 100000 ? 0 : 1;

            return sprintf(_x('%sm', 'number ending in m (millions)', 'quform'), number_format_i18n($count / 1000000, $precision));
        } else if ($count >= 1000) {
            $count = floor($count / 100) * 100;
            $precision = $count % 1000 < 100 ? 0 : 1;

            return sprintf(_x('%sk', 'number ending in k (thousands)', 'quform'), number_format_i18n($count / 1000, $precision));
        } else {
            return $count;
        }
    }

    /**
     * Base 64 encode the given data in a format safe for URLs
     *
     * Credit: http://php.net/manual/en/function.base64-encode.php#103849
     *
     * @param   mixed  $data
     * @return  string
     */
    public static function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Get all pages in an array formatted for select2
     *
     * @return array
     */
    public static function getPages()
    {
        $values = array();

        $pages = get_pages(array(
            'number' => 50,
            'sort_column' => 'post_modified',
            'sort_order' => 'DESC'
        ));

        if (is_array($pages) && count($pages)) {
            foreach ($pages as $page) {
                $values[] = array(
                    'id' => (string) $page->ID,
                    'text' => Quform::getPostTitle($page)
                );
            }
        }

        return $values;
    }

    /**
     * Get all posts in an array formatted for select2
     *
     * @return array
     */
    public static function getPosts()
    {
        $values = array();

        $posts = get_posts(array(
            'numberposts' => 50,
            'orderby' => 'modified',
            'order' => 'DESC'
        ));

        if (is_array($posts) && count($posts)) {
            foreach ($posts as $post) {
                $values[] = array(
                    'id' => (string) $post->ID,
                    'text' => Quform::getPostTitle($post)
                );
            }
        }

        return $values;
    }

    /**
     * Get the title of the given post
     *
     * @param   WP_Post  $post
     * @return  string
     */
    public static function getPostTitle($post)
    {
        $title = '';

        if ($post instanceof WP_Post) {
            $title = $post->post_title === '' ? sprintf(__('(no title) [%d]', 'quform'), $post->ID) : $post->post_title;
        }

        return $title;
    }
}
