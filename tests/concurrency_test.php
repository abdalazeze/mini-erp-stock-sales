<?php
/**
 * Concurrency test — fires 2 simultaneous sale requests for the last unit.
 * Only one should succeed; the other must get "insufficient stock".
 *
 * Usage:
 *   1. Set BASE_URL, credentials, and IDs below.
 *   2. Ensure the target product has exactly 1 unit in the target warehouse.
 *   3. php tests/concurrency_test.php
 */

define('BASE_URL',     'http://erp.local/');
define('LOGIN_URL',    BASE_URL . 'auth/login');
define('INVOICE_URL',  BASE_URL . 'invoices/save');

// Credentials for a user with access to WAREHOUSE_ID
define('USERNAME',     'admin');
define('PASSWORD',     'admin123');
define('CUSTOMER_ID',  1);
define('WAREHOUSE_ID', 1);
define('PRODUCT_ID',   4);   // seed: ELEC-004, qty=2 in WH-MAIN — run once to get to 1, then test

// ── helpers ──────────────────────────────────────────────────────────────────

function get_session_cookie($ch) {
    $info = curl_getinfo($ch);
    // cookie jar is handled per-handle via CURLOPT_COOKIEJAR / CURLOPT_COOKIEFILE
    return '';
}

function make_handle($cookie_file, $post_fields) {
    $ch = curl_init(INVOICE_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEFILE     => $cookie_file,
        CURLOPT_COOKIEJAR      => $cookie_file,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $post_fields,
    ]);
    return $ch;
}

function login($cookie_file) {
    // Fetch login page to grab CSRF token
    $ch = curl_init(LOGIN_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEJAR      => $cookie_file,
        CURLOPT_COOKIEFILE     => $cookie_file,
    ]);
    $html = curl_exec($ch);
    curl_close($ch);

    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m);
    $csrf = $m[1] ?? '';

    $ch = curl_init(LOGIN_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_POST           => true,
        CURLOPT_COOKIEJAR      => $cookie_file,
        CURLOPT_COOKIEFILE     => $cookie_file,
        CURLOPT_POSTFIELDS     => http_build_query([
            'csrf_token' => $csrf,
            'username'   => USERNAME,
            'password'   => PASSWORD,
        ]),
    ]);
    curl_exec($ch);
    curl_close($ch);
}

// ── main ─────────────────────────────────────────────────────────────────────

$cookie1 = sys_get_temp_dir() . '/erp_test_1.txt';
$cookie2 = sys_get_temp_dir() . '/erp_test_2.txt';

echo "Logging in as " . USERNAME . " (two sessions)...\n";
login($cookie1);
login($cookie2);

// Fetch CSRF tokens for each session
function get_csrf($cookie_file) {
    $ch = curl_init(BASE_URL . 'invoices/create');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_COOKIEFILE     => $cookie_file,
    ]);
    $html = curl_exec($ch);
    curl_close($ch);
    preg_match('/name="csrf_token"\s+value="([^"]+)"/', $html, $m);
    return $m[1] ?? '';
}

$csrf1 = get_csrf($cookie1);
$csrf2 = get_csrf($cookie2);

$fields = function ($csrf) {
    return http_build_query([
        'csrf_token'       => $csrf,
        'customer_id'      => CUSTOMER_ID,
        'warehouse_id'     => WAREHOUSE_ID,
        'discount_percent' => 0,
        'lines[0][product_id]'  => PRODUCT_ID,
        'lines[0][qty]'         => 1,
        'lines[0][unit_price]'  => 85.00,
    ]);
};

echo "Firing 2 simultaneous requests for product #" . PRODUCT_ID . "...\n";

$mh = curl_multi_init();
$ch1 = make_handle($cookie1, $fields($csrf1));
$ch2 = make_handle($cookie2, $fields($csrf2));
curl_multi_add_handle($mh, $ch1);
curl_multi_add_handle($mh, $ch2);

do { curl_multi_exec($mh, $running); } while ($running);

$r1 = curl_getinfo($ch1, CURLINFO_EFFECTIVE_URL);
$r2 = curl_getinfo($ch2, CURLINFO_EFFECTIVE_URL);

echo "Request 1 landed at: $r1\n";
echo "Request 2 landed at: $r2\n";

$ok    = substr_count($r1 . $r2, 'invoices/view');
$fails = 2 - $ok;
echo "Result: {$ok} succeeded, {$fails} rejected.\n";
echo ($ok === 1 && $fails === 1)
    ? "PASS — concurrency control working correctly.\n"
    : "UNEXPECTED — check stock level and re-run.\n";

curl_multi_remove_handle($mh, $ch1);
curl_multi_remove_handle($mh, $ch2);
curl_multi_close($mh);
@unlink($cookie1);
@unlink($cookie2);