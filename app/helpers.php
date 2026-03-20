<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function base_url(string $path = ''): string
{
    $path = ltrim($path, '/');
    return rtrim(BASE_URL, '/') . ($path !== '' ? '/' . $path : '');
}

function redirect(string $to): void
{
    header('Location: ' . $to);
    exit;
}

function slugify(string $text): string
{
    $text = trim($text);
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('~[^\pL\pN]+~u', '-', $text) ?? '';
    $text = trim($text, '-');
    if ($text === '') {
        $text = 'news';
    }
    return $text;
}

function start_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name(SESSION_NAME);
        session_start();
    }
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_validate(): void
{
    start_session();
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || $token === '' || !hash_equals($_SESSION['csrf'] ?? '', $token)) {
        http_response_code(403);
        echo 'Invalid CSRF token.';
        exit;
    }
}

function flash_set(string $key, string $message): void
{
    start_session();
    $_SESSION['flash'][$key] = $message;
}

function flash_get(string $key): ?string
{
    start_session();
    $msg = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return is_string($msg) ? $msg : null;
}

function validate_password_strength(string $password): ?string
{
    if (strlen($password) < 10) {
        return 'Password must be at least 10 characters.';
    }
    if (!preg_match('~[a-z]~', $password) || !preg_match('~[A-Z]~', $password) || !preg_match('~[0-9]~', $password)) {
        return 'Password must include uppercase, lowercase, and a number.';
    }
    return null;
}

/**
 * Render user-entered rich text safely (TinyMCE content).
 * Allows common formatting tags and safe attributes, strips scripts/events.
 */
function sanitize_rich_html(string $html): string
{
    $html = trim($html);
    if ($html === '') {
        return '';
    }

    // Fast-path: strip dangerous tags at minimum, then DOM-clean attributes.
    $allowedTags = '<p><br><b><strong><i><em><u><s><blockquote><pre><code><h1><h2><h3><h4><h5><h6><ul><ol><li><a><table><thead><tbody><tr><th><td><hr><span><div>';
    $html = strip_tags($html, $allowedTags);

    // If DOM extension is unavailable, return the stripped HTML only.
    if (!class_exists('DOMDocument')) {
        return $html;
    }

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML('<?xml encoding="utf-8"?>' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $xpath = new DOMXPath($dom);

    // Remove any remaining script/style elements if they slipped through.
    foreach ($xpath->query('//script|//style') as $node) {
        $node->parentNode?->removeChild($node);
    }

    // Strip event handler attributes and javascript: URLs.
    foreach ($xpath->query('//*[@*]') as $el) {
        if (!$el instanceof DOMElement) {
            continue;
        }
        $toRemove = [];
        foreach ($el->attributes as $attr) {
            $name = strtolower($attr->name);
            $value = trim($attr->value);
            if (str_starts_with($name, 'on')) {
                $toRemove[] = $attr->name;
                continue;
            }
            if (in_array($name, ['style'], true)) {
                $toRemove[] = $attr->name;
                continue;
            }
            if (in_array($name, ['src', 'href'], true)) {
                $v = strtolower($value);
                if (str_starts_with($v, 'javascript:') || str_starts_with($v, 'data:')) {
                    $toRemove[] = $attr->name;
                }
                continue;
            }
            if (!in_array($name, ['href', 'target', 'rel', 'colspan', 'rowspan'], true)) {
                $toRemove[] = $attr->name;
            }
        }
        foreach ($toRemove as $name) {
            $el->removeAttribute($name);
        }
    }

    // Enforce safe link attributes.
    foreach ($xpath->query('//a') as $a) {
        if (!$a instanceof DOMElement) {
            continue;
        }
        $href = $a->getAttribute('href');
        if ($href === '') {
            continue;
        }
        // Allow http(s), relative links, and anchors.
        if (!preg_match('~^(https?://|/|#)~i', $href)) {
            $a->removeAttribute('href');
        } else {
            $a->setAttribute('rel', 'nofollow noopener noreferrer');
            if ($a->getAttribute('target') === '') {
                $a->setAttribute('target', '_blank');
            }
        }
    }

    return $dom->saveHTML() ?: '';
}

