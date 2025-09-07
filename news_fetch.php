<?php
// news_fetch.php

require 'includes/db.php';

// NewsData API settings
$apiKey = 'pub_715f70fde66341d6858a346b6b341395';
$country = 'in'; // India
$url = "https://newsdata.io/api/1/news?apikey=$apiKey&country=$country";

// Proxy settings
$proxyHost = 'hostelinternet.rgukt.ac.in';
$proxyPort = 3128;
$proxyUser = 'b211516';
$proxyPass = 'EL5P49';

// Log file (absolute path to avoid cron issues)
$logFile = '/opt/lampp/htdocs/dailyscope/news_fetch.log';

// Function to check if proxy is reachable
function isProxyAvailable($host, $port, $timeout = 2) {
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    }
    return false;
}

// Function to fetch news (with or without proxy)
function fetchNews($url, $useProxy = false, $proxyHost = '', $proxyPort = '', $proxyUser = '', $proxyPass = '') {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

    if ($useProxy) {
        curl_setopt($ch, CURLOPT_PROXY, $proxyHost . ':' . $proxyPort);
        curl_setopt($ch, CURLOPT_PROXYUSERPWD, "$proxyUser:$proxyPass");
        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
    }

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    return [$response, $error];
}

// Try with proxy first if available
$response = null;
$error = null;

if (isProxyAvailable($proxyHost, $proxyPort)) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Trying with proxy $proxyHost:$proxyPort\n", FILE_APPEND);
    list($response, $error) = fetchNews($url, true, $proxyHost, $proxyPort, $proxyUser, $proxyPass);

    if ($error) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Proxy failed: $error. Retrying direct...\n", FILE_APPEND);
        list($response, $error) = fetchNews($url); // retry direct
    }
} else {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Proxy not available, using direct connection\n", FILE_APPEND);
    list($response, $error) = fetchNews($url);
}

// Final error check
if ($error) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - ❌ Final cURL Error: $error\n", FILE_APPEND);
    exit;
}

// Parse response
$data = json_decode($response, true);
if (empty($data['results'])) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - No news found.\n", FILE_APPEND);
    exit;
}

// Insert news into DB
foreach ($data['results'] as $news) {
    $title = $news['title'] ?? '';
    $description = $news['description'] ?? '';
    $url_link = $news['link'] ?? '';
    $image = $news['image_url'] ?? '';
    $category = isset($news['category']) ? implode(',', $news['category']) : 'general';
    $publishedAt = date('Y-m-d H:i:s', strtotime($news['pubDate'] ?? 'now'));

    $stmt = $conn->prepare("INSERT IGNORE INTO news (title, description, url, image, category, publishedAt) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $title, $description, $url_link, $image, $category, $publishedAt);
    $stmt->execute();
}

// Success log
file_put_contents($logFile, date('Y-m-d H:i:s') . " - ✅ News updated successfully.\n", FILE_APPEND);
echo "✅ News updated successfully.\n";
?>

