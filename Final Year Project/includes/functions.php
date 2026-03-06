<?php
/**
 * includes/functions.php
 * Core functions for security and utility.
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF Token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Verify CSRF Token
 * @param string $token
 * @return bool
 */
function verify_csrf_token($token) {
    if (isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token)) {
        return true;
    }
    return false;
}

/**
 * Sanitize HTML output to prevent XSS
 * @param string $string
 * @return string
 */
function sanitize_html($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Base URL helper for robust routing
 * @param string $path
 * @return string
 */
function base_url($path = '') {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    // In our local environment, we assume 'Final Year Project' is mapped properly or accessed relatively.
    // To make this universal without knowing virtual hosts:
    // Let's deduce the script folder path dynamically:
    $script_dir = dirname($_SERVER['SCRIPT_NAME']);
    // If we're inside /modules/user/login.php, dirname is /modules/user.
    // A more solid approach is defining a hardcoded BASE_URL. For generic XAMPP:
    // Let's assume the project root is where index.php is accessed.

    // Fallback: Just return abs path from root for now, or just use relative paths.
    // For simplicity, let's just make it relative to root using a global constant if we wanted.
    // We will assume the site is hosted at the root or we compute base from an array slice.

    // Instead of complex logic, I'll return an absolute URL using a pre-defined root if needed.
    // Force normalize ALL directory separators to forward slashes for Windows XAMPP environments
    $doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
    $dir = str_replace('\\', '/', dirname(__DIR__)); // Get project root folder

    // Subtract document root string length from the current absolute directory string to get the web-relative subdirectory
    $relative_path = str_replace($doc_root, '', $dir);

    // Clean up slashes
    $relative_path = '/' . trim($relative_path, '/');
    if ($relative_path === '/') $relative_path = '';

    return $protocol . '://' . $host . $relative_path . '/' . ltrim($path, '/');
}
?>
