<?php
add_action('admin_menu', 'itais_create_menu');

function itais_create_menu()
{
    //create new top-level menu
    add_menu_page('JSON Proxy Cache', 'JSON Proxy Cache', 'administrator', __FILE__, 'itais_settings_page', plugins_url('/images/icon-16x16.png', __FILE__));
    //call register settings function
    add_action('admin_init', 'register_jcisettings');
}

function register_jcisettings()
{
    //register our settings
    register_setting('jpc-options', 'itais_json_url');
    register_setting('jpc-options', 'itais_enable_cache');
    register_setting('jpc-options', 'itais_cache_time');
    register_setting('jpc-options', 'itais_cache_time_format');
    register_setting('jpc-options', 'itais_oauth_bearer_access_key');
    register_setting('jpc-options', 'itais_http_header_default_useragent');
    register_setting('jpc-options', 'itais_api_errorhandling');
}

function itais_settings_page()
{
    ?>
<style type="text/css">
  .leftsettings {   width: 70%;  float:left;   }
</style>
<div class="leftsettings">
<h2>JSON Content Importer: Settings</h2>
<form method="post" action="options.php">
    <?php settings_fields('jpc-options'); ?>
    <?php do_settings_sections('jpc-options'); ?>
    <table class="form-table">
        <tr>
        	<td colspan="2">
    <?php submit_button(); ?>
          <strong>Enable Cache:</strong> <input type="checkbox" name="itais_enable_cache" value="1" <?php echo (get_option('itais_enable_cache') == 1)?"checked=checked":""; ?> />
        	 &nbsp;&nbsp;&nbsp; reload json from web - if cachefile is older than <input type="text" name="itais_cache_time" size="2" value="<?php echo get_option('itais_cache_time'); ?>" />
           <select name="itais_cache_time_format">
           			<option value="minutes" <?php echo (get_option('itais_cache_time_format') == 'minutes')?"selected=selected":""; ?>>Minutes</option>
                    <option value="days" <?php echo (get_option('itais_cache_time_format') == 'days')?"selected=selected":""; ?>>Days</option>
                    <option value="month" <?php echo (get_option('itais_cache_time_format') == 'month')?"selected=selected":""; ?>>Months</option>
                    <option value="year" <?php echo (get_option('itais_cache_time_format') == 'year')?"selected=selected":""; ?>>Years</option>
           </select>
           </td>
        </tr>
        <tr>
        	<td colspan="2">
          <strong>Handle unavailable  APIs:</strong>
		  <br>
		  <?php
            $pluginOption_itais_api_errorhandling = get_option('itais_api_errorhandling');
    if (empty($pluginOption_itais_api_errorhandling)) {
        update_option('itais_api_errorhandling', 0);
        $pluginOption_itais_api_errorhandling = 0;
    } ?>

			If the request to an API to get JSON fails, the plugin can try to use a maybe cached JSON (fill the cache at least once with a successful API-request):<br>
		  <input type="radio" name="itais_api_errorhandling" value="0" <?php echo ($pluginOption_itais_api_errorhandling == 0)?"checked=checked":""; ?> />
		  do not try to use cached JSON<br>
		  <input type="radio" name="itais_api_errorhandling" value="1" <?php echo ($pluginOption_itais_api_errorhandling == 1)?"checked=checked":""; ?> />
		  If the API-http-answercode is not 200: try to use cached JSON<br>
		  <input type="radio" name="itais_api_errorhandling" value="2" <?php echo ($pluginOption_itais_api_errorhandling == 2)?"checked=checked":""; ?> />
		  If the API sends invalid JSON: try to use cached JSON<br>
		  <input type="radio" name="itais_api_errorhandling" value="3" <?php echo ($pluginOption_itais_api_errorhandling == 3)?"checked=checked":""; ?> />
		  Recommended (not switched on due to backwards-compatibility):<br>If the API-http-answercode is not 200 OR sends invalid JSON: try to use cached JSON<br>
            </td>
        </tr>
        <tr>
        	<td colspan="2">
        <strong>Complete url to the api to cache</strong>
        <br>
           <input type="text" name="itais_json_url" value="<?php echo get_option('itais_json_url'); ?>" size="60"/>
           </td>
        </tr>

        <tr>
        	<td colspan="2">
        <strong>oAuth Bearer accesskey: passed in header as "Authorization: Bearer accesskey"<br>(add "nobearer " - mind the space! - if you want to pass "Authorization:accesskey"):</strong>
        <br>
           <input type="text" name="itais_oauth_bearer_access_key" value="<?php echo get_option('itais_oauth_bearer_access_key'); ?>" size="60"/>
           </td>
        </tr>

        <tr>
        	<td colspan="2">
          <strong>Send default Useragent (some APIs need that):</strong> <input type="checkbox" name="itais_http_header_default_useragent" value="1" <?php echo (get_option('itais_http_header_default_useragent') == 1)?"checked=checked":""; ?> />
            </td>
        </tr>


    </table>
    </form>
</div>
<?php
}


?>