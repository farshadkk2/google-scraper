<?php

namespace App;

use Exception;

class GoogleSearch extends Google
{
    private string $query;
    private string $type = "desktop";
    private string $language = "en";
    private int $start = 0;
    private string $domain = "com";

    private $proxy;
    private $auth;

    function __construct()
    {
        $this->dataDir = __DIR__ . DIRECTORY_SEPARATOR;
    }

    /**
     * Sets the search term to be used in any queries that follow. Resets crawl start position to 0.
     * @param string $query Search word or phrase.
     */
    function setQuery(string $query)
    {
        $this->query = $query;
        $this->start = 0;
    }

    /**
     * Sets the language to be used in any queries that follow. Resets crawl start position to 0.
     * @param Language $language A language object with a code.
     */
    function setLanguage(Language $language)
    {
        $this->language = $language->code;
        $this->start = 0;
    }

    /**
     * Sets the Google domain extension (www.google.___). Resets crawl start position to 0.
     * @param string $domain Domain extension from getDomains() (examples: com, com.au, ws)
     */
    function setDomain($domain = "com")
    {
        $this->domain = $domain;
        $this->start = 0;
    }

    /**
     * Sets the type of device to emulate using request user agents. Resets crawl start position to 0.
     * @param string $deviceType (accepted values: desktop, mobile, tablet)
     * @throws Exception when device type is invalid.
     */
    function setDevice($deviceType = "desktop")
    {
        if (!isset($this->userAgents[$deviceType])) throw new Exception("Invalid device type '$deviceType'");

        $this->type = $deviceType;
        $this->start = 0;
    }

    /**
     * Modifies the start parameter for the session. This is set automatically to the next page when you call next().
     * @param int $start Search results starting from the nth row.
     */
    function setStart($start = 0)
    {
        $this->start = $start;
    }

    /**
     * Sets the proxy to use when connecting to Google.
     * @param string $proxy IP:port of the proxy or blank to disable proxying.
     * @param string $auth  Optional user:password for the proxy authentication.
     */
    function setProxy($proxy = "", $auth = "")
    {
        $this->proxy = $proxy;
        $this->auth = $auth;
    }

    /**
     * Fetches the next page from the search session.
     * @throws Exception on failure with a message explaining the cause.
     * @return GoogleResults|null object containing the results from the next page or null if no results.
     */
    function next(): ?GoogleResults
    {
        $url = $this->baseurl;
        $url = str_replace("{:tld}", $this->domain, $url);
        $url = str_replace("{:language}", urlencode($this->language), $url);
        $url = str_replace("{:query}", urlencode($this->query), $url);
        $url = str_replace("{:start}", $this->start, $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgents[$this->type]);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_REFERER, $this->referrer);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($this->proxy) curl_setopt($ch, CURLOPT_PROXY, $this->proxy);
        if ($this->auth) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $this->auth);

        $data = @curl_exec($ch);

        if (curl_errno($ch) > 0) {
            throw new Exception("Failed to connect to Google: curl error: " . curl_error($ch), 1);
        }

        if (defined("CURLINFO_HTTP_CODE")) {
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($code != 200) throw new Exception("Received an invalid http code ($code) from Google.", 2);
        }

        curl_close($ch);

        if (strpos($data, "<p style=\"padding-top:.33em\">") !== false) return null;

        $res = new GoogleResults($data);

        $res->query = $this->query;
        $res->start = $this->start;
        $res->url = $url;

        if ($res->num_results == 0) return null;
        $this->start += 10;

        return $res;
    }
}
