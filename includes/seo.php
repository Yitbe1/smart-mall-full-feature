<?php
/**
 * SEO Helper Functions for Smart Mall
 * Lightweight Open Graph, Twitter Card, canonical URL, and JSON-LD breadcrumb utilities.
 */

/**
 * Detect the current page URL from $_SERVER.
 */
function seo_current_url(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri    = $_SERVER['REQUEST_URI'] ?? '/';
    return $scheme . '://' . $host . $uri;
}

/**
 * Output Open Graph and Twitter Card meta tags.
 *
 * @param string $title       Page title (defaults to $GLOBALS['page_title'])
 * @param string $description Page description (defaults to $GLOBALS['page_description'])
 * @param string $url         Canonical URL (defaults to current URL)
 * @param string $image       OG image URL
 */
function seo_og_tags(string $title = '', string $description = '', string $url = '', string $image = ''): void
{
    if (empty($title) && !empty($GLOBALS['page_title'])) {
        $title = $GLOBALS['page_title'];
    }
    if (empty($description) && !empty($GLOBALS['page_description'])) {
        $description = $GLOBALS['page_description'];
    }
    if (empty($url)) {
        $url = seo_current_url();
    }
    if (empty($image)) {
        $image = '/reference/assets/images/logo-icon.png';
    }

    $title       = htmlspecialchars($title);
    $description = htmlspecialchars($description);
    $image       = htmlspecialchars($image);
    $url         = htmlspecialchars($url);

    echo "    <meta property=\"og:title\" content=\"{$title}\">\n";
    echo "    <meta property=\"og:description\" content=\"{$description}\">\n";
    echo "    <meta property=\"og:url\" content=\"{$url}\">\n";
    echo "    <meta property=\"og:image\" content=\"{$image}\">\n";
    echo "    <meta property=\"og:type\" content=\"website\">\n";
    echo "    <meta name=\"twitter:card\" content=\"summary_large_image\">\n";
    echo "    <meta name=\"twitter:title\" content=\"{$title}\">\n";
    echo "    <meta name=\"twitter:description\" content=\"{$description}\">\n";
    echo "    <meta name=\"twitter:image\" content=\"{$image}\">\n";
}

/**
 * Output <link rel="canonical"> tag.
 *
 * @param string $url Canonical URL (defaults to current URL)
 */
function seo_canonical(string $url = ''): void
{
    if (empty($url)) {
        $url = seo_current_url();
    }
    echo '    <link rel="canonical" href="' . htmlspecialchars($url) . '">' . "\n";
}

/**
 * Output JSON-LD breadcrumb structured data.
 *
 * @param array $crumbs List of associative arrays with 'label' and 'url' keys
 */
function seo_jsonld_breadcrumb(array $crumbs): void
{
    if (empty($crumbs)) {
        return;
    }

    $items = [];
    foreach ($crumbs as $i => $crumb) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $crumb['label'],
            'item'     => $crumb['url'],
        ];
    }

    $json = json_encode([
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $items,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

    echo "    <script type=\"application/ld+json\">{$json}</script>\n";
}
