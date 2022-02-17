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

    /**
     * Finds a letter at a certain point on the alphabet used in Google's secret key.
     * @param int $startNumber    A base offset position.
     * @param string $startLetter The letter to match to your $startNumber.
     * @param int $findNumber     The number, above $startNumber, to find on the alphabet.
     * @return string The letter.
     */
    function getLetterFromAlphabet($startNumber = 1, $startLetter = "a", $findNumber = 2): string
    {
        $alpha = ['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];

        $offset = 0;
        foreach ($alpha as $i => $letter) {
            if ($letter == $startLetter) $offset = $i;
        }

        $difference = $findNumber - $startNumber;
        return $alpha[$offset + $difference];
    }
}
