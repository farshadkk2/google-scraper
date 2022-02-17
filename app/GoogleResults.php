<?php

namespace App;

class GoogleResults
{
    public string $url;
    public string $query;

    public int $num_results = 0;

    public array $places = [];
    public array $results = [];

    private int $i = 0;

    function __construct($html)
    {
        // parse number of results
        $results = $this->getTextBetween("<div id=\"result-stats\">", "<", $html);
        if (isset($results[0])) {
            $results = preg_replace("/[^0-9]/", "", $results[0]);
            $this->num_results = (int) $results;
        }

        // find app bars for very popular results (and assume 1 million results if necessary)
        if (!$this->num_results && strpos($html, '<div class="appbar" id="appbar">') !== false) {
            $this->num_results = 1000000;
        }

        // parse search results
        $results = $this->getTextBetween("<div class=\"rc\"", "</div><!--n-->", $html);
        $serp = [];

        foreach ($results as $result) {
            $url = $this->getTextBetween("<a href=\"", "\"", $result);
            $title = $this->getTextBetween("<br><h3 class=\"", "</h3>", $result);
            $site = $this->getTextBetween("<cite", "</cite>", $result);
            $description = $this->getTextBetween("<span class=\"st\">", "*", "$result*");

            if (count($url) == 0) continue;
            if (count($title) == 0) continue;
            if (count($site) == 0) continue;
            if (count($description) == 0) continue;

            // Adjust title
            $title = substr($title[0], strpos($title[0], '">') + 2);

            // Adjust site
            $site = substr($site[0], strpos($site[0], '">') + 2);
            if (stripos($site, '&rsaquo;') !== false) $site = substr($site, 0, stripos($site, '&rsaquo;'));

            $url = $url[0];
            $site = strip_tags($site);
            $description = strip_tags($description[0]);

            if (stripos($site, "://") === false) $site = "http://" . $site;
            $purl = @parse_url($site);

            if (isset($purl['host'])) $domain = $purl['host'];
            else $domain = $site;

            if (!$site || !$description) continue;
            if (strpos($title, "<g-img") !== false) continue;

            $serp[] = [
                "title" => html_entity_decode($title),
                "site" => html_entity_decode($site),
                "url" => $url,
                "domain" => $domain,
                "description" => html_entity_decode($description)
            ];
        }

        $this->results = $serp;

        // parse google places
        $items = $this->getTextBetween("jsl=\"\$x 1;\"><div class", "</div><!--n-->", $html);
        $places = [];
        foreach ($items as $item) {
            if (strpos($item, 'Why this ad?') !== false) {
                continue;
            }

            $heading = strip_tags($this->getTextBetween("role=\"heading\"><span>", "</span></div>", $item)[0]);
            $description = strip_tags($this->getTextBetween("&middot; ", "</div>", $item)[0]);
            $location = $this->getTextBetween("<div><span>", "</span>", $item);
            $location = count($location) > 0 ? strip_tags($location[0]) : '';

            $places[] = [
                "heading" => $heading,
                "description" => $description,
                "location" => $location
            ];
        }

        $this->places = $places;
    }

    /**
     * Internal function for fetching an array of all matching [start]...[end] strings.
     * @param $a
     * @param $b
     * @param $str
     * @return array
     */
    private function getTextBetween($a, $b, $str): array
    {
        $start = 0;
        $matches = [];

        while (($start = strpos($str, $a, $start)) !== false) {
            if ($str == "<div class=\"_M4k\">") echo $start;
            $end = strpos($str, $b, $start + strlen($a));
            if ($end === false) break;

            $matches[] = trim(substr($str, $start + strlen($a), $end - ($start + strlen($a))));
            $start += strlen($a) + strlen($b);
        }

        return $matches;
    }

    /**
     * Get the next search result on the current page as an associative array (containing title, site, domain, description).
     * @return mixed array of next result or null if no more rows.
     */
    function fetch_array(): mixed
    {
        $i = $this->i++;

        if (!isset($this->results[$i])) {
            return null;
        }

        return $this->results[$i];
    }

    /**
     * Get the next search result on the current page as an stdObject (containing title, site, domain, and description properties).
     * @return object|null object of next result set or null if no more rows.
     */
    function fetch_object(): ?object
    {
        $i = $this->i++;

        if (!isset($this->results[$i])) {
            return null;
        }

        return (object) $this->results[$i];
    }
}
