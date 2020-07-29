<?php
/*
Plugin Name: JSON Proxy Cache
Plugin URI: https://github.com/jredrejo/meridabadajoz
Description: Plugin to import, cache and return  a JSON-Feed
Version: 0.0.1
Author: José L. Redrejo Rodríguez
Author URI: https://www.itais.net
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/* block direct requests */
if (!function_exists('add_action')) {
    echo 'Hello, this is a plugin: You must not call me directly.';
    exit;
}
defined('ABSPATH') or exit;

// if(!class_exists('JsonContentImporter')){
//     require_once plugin_dir_path( __FILE__ ) . '/class-json-content-importer.php';
// }
require_once plugin_dir_path(__FILE__) . '/options.php';
// $JsonContentImporter = new JsonContentImporter();
class JsonContentImporter
{
    private $feedUrl = ""; # url of JSON-Feed
    private $urlgettimeout = 5; # 5 sec default timeout for http-url
    /* internal */
    private $cacheFile = "";
    private $feedData = "";
    private $cacheFolder;
    private $debugmode = 0; # 10: show debug-messages
    private $cacheExpireTime = 0;
    private $oauth_bearer_access_key = "";
    private $http_header_default_useragent_flag = 0;
    private $fallback2cache = 0;

    public function apiRequestExecute()
    {
        $this->feedUrl = $this->removeInvalidQuotes(get_option('itais_json_url'));
        if (get_option('itais_api_errorhandling') >= 0) {
            $this->fallback2cache = get_option('itais_api_errorhandling');
        }
        if ("1" == $fallback2cache || "2" == $fallback2cache || "3" == $fallback2cache) {
            $this->fallback2cache = $fallback2cache;
        }

        /* caching or not? */
        if ((!class_exists('FileLoadWithCache'))) {
            require_once plugin_dir_path(__FILE__) . '/class-fileload-cache.php';
        }

        $this->cacheFolder = WP_CONTENT_DIR . '/cache/jsonproxycache/';
        # cachefolder ok: set cachefile
        $this->cacheFile = $this->cacheFolder . sanitize_file_name(md5($this->feedUrl)) . ".cgi"; # cache json-feed
        if (get_option('itais_enable_cache') == 1) {
            # 1 = checkbox "enable cache" activ
            $this->cacheEnable = true;
            # check cacheFolder
            new CheckCacheFolder(WP_CONTENT_DIR . '/cache/', $this->cacheFolder);
        } else {
            # if not=1: no caching
            $this->cacheEnable = false;
        }

        /* cache */
        $this->cacheEnable = false;
        if (get_option('itais_enable_cache') == 1) {
            $this->cacheEnable = true;
        }
        $cacheTime = get_option('itais_cache_time'); # max age of cachefile: if younger use cache, if not retrieve from web
        $format = get_option('itais_cache_time_format');
        $cacheExpireTime = strtotime(date('Y-m-d H:i:s', strtotime(" -" . $cacheTime . " " . $format)));
        $this->cacheExpireTime = $cacheExpireTime;

        $this->oauth_bearer_access_key = get_option('itais_oauth_bearer_access_key');
        $this->http_header_default_useragent_flag = get_option('itais_http_header_default_useragent');

        $fileLoadWithCacheObj = new FileLoadWithCache($this->feedUrl, $this->urlgettimeout, $this->cacheEnable, $this->cacheFile, $this->cacheExpireTime, $this->oauth_bearer_access_key, $this->http_header_default_useragent_flag, $this->debugmode, $this->fallback2cache);
        $fileLoadWithCacheObj->retrieveJsonData();
        $this->feedData = $fileLoadWithCacheObj->getFeeddata();
    }
    private function removeInvalidQuotes($txtin)
    {
        $invalid1 = urldecode("%E2%80%9D");
        $invalid2 = urldecode("%E2%80%B3");
        $txtin = preg_replace("/^[" . $invalid1 . "|" . $invalid2 . "]*/i", "", $txtin);
        $txtin = preg_replace("/[" . $invalid1 . "|" . $invalid2 . "]*$/i", "", $txtin);
        return $txtin;
    }

    public function getFeeddata()
    {
        $tmpJSON = json_decode($this->feedData, true);
        if (is_null($tmpJSON)) {
            # utf8_encode JSON-datastring, then try json_decode again
            $tmpJSON2 = json_decode(utf8_encode($this->feedData));
            if (is_null($tmpJSON2)) {
                $tmpJSON2 = "";
            }
        }
        return $tmpJSON;
    }
}

function jr_cached()
{
    $JsonContentImporter = new JsonContentImporter();
    $JsonContentImporter->apiRequestExecute();
    // return $JsonContentImporter->getFeeddata();
    return new WP_REST_Response($JsonContentImporter->getFeeddata(), 200);
}

add_action('rest_api_init', function () {
    register_rest_route('jr/v1', 'proxy', ['methods' => 'GET', 'callback' => 'jr_cached']);
});
