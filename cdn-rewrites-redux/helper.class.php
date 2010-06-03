<?php
class CDN_Rewrites_Helper
{
	var $config;
	
	function CDN_Rewrites_Helper()
	{
		$this->__construct();
	}

	function __construct()
	{
		global $cdn_rewrites_config;
		$this->config = $cdn_rewrites_config;
	}
    
    function str_replace_once($needle, $replace, $haystack) 
    {
        $pos = strpos($haystack, $needle);
        if ($pos === false) 
        {
            return $haystack;
        }
        return substr_replace($haystack, $replace, $pos, strlen($needle));
    } 
	
	function log($str)
	{
        if (is_array($str))
        {
            $str = print_r($str, true);
        }
        
		$handle = fopen(dirname(__FILE__) . '/log.log', 'a+');
		fwrite($handle, "$str\n");
		fclose($handle);
	}
    
    function rule_instructs()
    {
        return '
        <div id="cndrhelp" style="background: #ffffe0; padding: 5px 10px; margin-bottom: 5px; width: 600px; border: 2px solid #ffa500">
            Remember that <br />
            - Both fields must end with a slash (<code>/</code>) <br />
            - The destination host should be in full format (with <code>http://</code> or <code>https://</code> prefixed) <br />
            Examples: <br />
            - Rewrite <code>/</code> into <code>http://static.yoursite.com/</code><br />
            - Rewrite <code>http://www.yoursite.com/</code> into <code>http://images.yoursite.com/</code>
        </div>';
    }
    
    function ajax_error($msg = '')
    {
        header('HTTP/1.0 404 Not Found');
        echo $msg;
        die();
    }
}