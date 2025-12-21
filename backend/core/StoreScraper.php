<?php
class StoreScraper
{
    public static function fetchMetadata($url)
    {
        // GitHub Proje veya Profil
        if (strpos($url, 'github.com') !== false) {
            $parts = explode('/', trim(parse_url($url, PHP_URL_PATH), '/'));
            if (count($parts) >= 2) {
                return self::fetchGitHub($url);
            } else {
                return self::fetchGitHubUserRepos($url);
            }
        }

        // Apple Store API ile Uygulama Çekme
        if (strpos($url, 'apps.apple.com') !== false) {
            return self::fetchAppleAPI($url);
        }

        // Play Store Scraper ile Uygulama Çekme
        if (strpos($url, 'play.google.com') !== false) {
            return self::fetchScraping($url);
        }

        return false;
    }

    private static function fetchGitHubUserRepos($url)
    {
        $path = trim(parse_url($url, PHP_URL_PATH), '/');
        if (empty($path))
            return false;

        $username = explode('/', $path)[0];
        $apiUrl = "https://api.github.com/users/{$username}/repos?sort=updated&per_page=100";

        $json = self::curlGet($apiUrl);
        if (!$json)
            return false;

        $data = json_decode($json, true);
        if (!is_array($data) || isset($data['message']))
            return false;

        $repos = [];
        foreach ($data as $repo) {
            if ($repo['fork'])
                continue;

            $repos[] = [
                'name' => $repo['name'],
                'description' => self::shortenText($repo['description'] ?? ''),
                'language' => $repo['language'] ?? 'Unknown',
                'stars' => $repo['stargazers_count'] ?? 0,
                'forks' => $repo['forks_count'] ?? 0,
                'url' => $repo['html_url'],
                'icon' => 'github'
            ];
        }
        return ['type' => 'github_user', 'repos' => $repos];
    }

    private static function fetchGitHub($url)
    {
        preg_match('/github\.com\/([^\/]+)\/([^\/^\?]+)/', $url, $matches);
        if (count($matches) < 3)
            return false;

        $owner = $matches[1];
        $repo = $matches[2];
        $apiUrl = "https://api.github.com/repos/{$owner}/{$repo}";

        $json = self::curlGet($apiUrl);
        if (!$json)
            return false;

        $data = json_decode($json, true);
        if (isset($data['message']) && $data['message'] == 'Not Found')
            return false;

        return [
            'name' => $data['name'] ?? '',
            'description' => self::shortenText($data['description'] ?? ''),
            'language' => $data['language'] ?? 'Unknown',
            'stars' => $data['stargazers_count'] ?? 0,
            'forks' => $data['forks_count'] ?? 0,
            'url' => $data['html_url'] ?? $url,
            'icon' => 'github'
        ];
    }

    private static function fetchAppleAPI($url)
    {
        preg_match('/id(\d+)/', $url, $matches);
        if (empty($matches[1]))
            return false;

        $appId = $matches[1];
        preg_match('/\.com\/([a-z]{2})\/app/', $url, $countryMatches);
        $country = !empty($countryMatches[1]) ? $countryMatches[1] : 'us';

        $apiUrl = "https://itunes.apple.com/lookup?id={$appId}&country={$country}";
        $json = self::curlGet($apiUrl);
        if (!$json)
            return false;

        $data = json_decode($json, true);
        if (empty($data['results'][0]))
            return false;

        $res = $data['results'][0];
        $icon = $res['artworkUrl512'] ?? $res['artworkUrl100'] ?? '';

        if ($icon) {
            $icon = str_replace(['100x100bb', '200x200bb'], '512x512bb', $icon);
            if (strpos($icon, '.webp') !== false)
                $icon = str_replace('.webp', '.jpg', $icon);
        }

        return [
            'name' => $res['trackCensoredName'] ?? $res['trackName'] ?? '',
            'description' => self::shortenText($res['description'] ?? ''),
            'icon' => $icon
        ];
    }

    private static function fetchScraping($url)
    {
        $html = self::curlGet($url);
        if (!$html)
            return false;

        libxml_use_internal_errors(true);
        $doc = new DOMDocument();
        @$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new DOMXPath($doc);

        $name = self::queryValue($xpath, "//meta[@property='og:title']/@content");
        $desc = self::queryValue($xpath, "//meta[@property='og:description']/@content");
        $icon = self::queryValue($xpath, "//meta[@property='og:image']/@content");

        if (strpos($url, 'play.google.com') !== false) {
            // Google Play başlık temizliği (Daha agresif temizlik)
            $name = str_replace(' - Apps on Google Play', '', $name);
            $name = str_replace(' - Google Play’de Uygulamalar', '', $name);
            $name = str_replace(' - Google Play', '', $name);
            $name = trim($name);

            if ($icon && strpos($icon, '=w') !== false)
                $icon = explode('=w', $icon)[0] . '=w512';
        }

        return [
            'name' => $name,
            'description' => self::shortenText($desc),
            'icon' => $icon
        ];
    }

    private static function shortenText($text, $limit = 160)
    {
        if (!$text)
            return '';
        $text = strip_tags($text);
        if (mb_strlen($text) <= $limit)
            return $text;
        $text = mb_substr($text, 0, $limit);
        $lastSpace = mb_strrpos($text, ' ');
        return ($lastSpace !== false ? mb_substr($text, 0, $lastSpace) : $text) . '...';
    }

    private static function curlGet($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36');
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    private static function queryValue($xpath, $query)
    {
        $node = $xpath->query($query);
        return ($node && $node->length > 0) ? trim($node->item(0)->nodeValue) : '';
    }
}
?>