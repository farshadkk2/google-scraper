<?php

namespace App;

class Google
{
    /**
     * The directory to the scrape data files. Should end with a slash (/). Blank for current directory.
     */
    public string $dataDir = "";

    /**
     * User agents to use when making HTTP requests to Google.
     */
    public array $userAgents = [
        "desktop" => "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.80 Safari/537.36",
        "mobile" => "Mozilla/5.0 (iPhone; U; CPU iPhone OS 5_1_1 like Mac OS X; en) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/9B206 Safari/7534.48.3",
        "tablet" => "Mozilla/5.0 (iPad; U; CPU OS 5_1_1 like Mac OS X; en-us) AppleWebKit/534.46.0 (KHTML, like Gecko) CriOS/19.0.1084.60 Mobile/9B206 Safari/7534.48.3"
    ];


    public string $referrer = "https://www.google.com/";

    /**
     * The base URL with variables (tld, language, query, start) encoded as {:variable_name} which will be replaced before executing.
     */
    public string $baseurl = "https://www.google.{:tld}/search?ie=UTF-8&oe=UTF-8&hl={:language}&q={:query}&start={:start}";


    /**
     * Parses the domains list.
     * @return array<string> An array containing the domain extensions available.
     */
    function getDomains(): array
    {
        return explode("\n", trim(file_get_contents($this->dataDir . "domains.txt")));
    }

    /**
     * Parses the languages list.
     * @return array<array> An array containing arrays (in format {code: "fa", name: "Persian'"}) for available languages.
     */
    function getLanguages(): array
    {
        $data = trim(file_get_contents($this->dataDir . "languages.txt"));
        $lines = explode("\n", $data);
        $languages = [];

        foreach ($lines as $line) {
            $sep = strpos($line, " ");
            $languages[] = new Language(trim(substr($line, 0, $sep)), trim(substr($line, $sep + 1)));
        }

        return $languages;
    }
}
