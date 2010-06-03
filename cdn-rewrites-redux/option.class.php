<?php
class CDN_Rewrites_Option
{
	var $config;
	
	function CDN_Rewrites_Option()
	{
		$this->__construct();
	}

	function __construct()
	{
		global $cdn_rewrites_config;
		$this->config = $cdn_rewrites_config;
	}
	
	function get()
	{
        static $options;
        
        if (!isset($options))
        {
		    $options = get_option($this->config['plugin_name']);
		    if (empty($options))
		    {
			    $options = $this->config['default_options'];
		    }
        }
		
		return $options;
	}
    
    function panel()
    {
        $options = $this->get();
        
        return sprintf('<div id="otherDiv">
            <form action="index.php" method="post" class="cdnr_ajax" autocomplete="off" ftype="options" style="width: 750px; float: left">
                <ul>
                    <li><input type="checkbox" %s name="debug" id="debug" /><label for="debug">Debug mode (only list down to-be-rewritten URL\'s, not applying them)</label></li>
                </ul>
                <h3>Support this plugin (Thanks!)</h3>
                <ul>
                    <li><input type="checkbox" %s name="powered" id="powered"><label for="powered">Show &quot;Powered by <a href="http://www.amuxbit.co./cdn-rewrites-redux/">CDN Rewrites Redux</a>, Based on <a href="http://www.phoenixheart.net/wp-plugins/cdn-rewrites/">CDN Rewrites</a>&quot; message on page footer</label> 
                </ul>
                <h3>Other support options</h3>
                <ul id="otherSupport">
                    <li>Blog or <a href="http://twitter.com/home/?status=I+am+using+this+awesome+WordPress+CDN+Rewrites+plugin+by+%%40Phoenixheart+http%%3A%%2F%%2Fbit.ly%%2101Vzv" target="_blank">Tweet about it</a>.
                    You can also <a href="http://twitter.com/Phoenixheart" target="_blank">follow me on Twitter</a></li>
					<li><a href="http://buysellads.com/buy/detail/2211">Buy an ad slot</a> from <a href="http://www.phoenixheart.net">my site</a></li>
                    <li>Give this plugin a good rating on <a href="http://wordpress.org/extend/plugins/free-cdn/" target="_blank">WordPress Codex</a></li>
                    <li>Check out <a href="http://www.phoenixheart.net/wp-plugins/" target="_blank">my other plugins</a></li>
                    <li><a href="http://feeds.feedburner.com/phoenixheart" target="_blank">Subscribe</a> to my RSS</li>
                    <li>Send me at phoenixheart (Gmail) an email telling that you like my plugin ;)</li>
                </ul>
                 <input type="hidden" name="_nonce" value="%s" />
                 <input type="hidden" name="cdnr_action" value="save_options" />
                 <p class="submit">
                    <input class="button-primary" type="submit" value="Save Options" name="submit"/>
                 </p>
            </form>
            <div id="donate">
                <p>CDN Rewrites is totally free, but if you think it worths some love, please consider a donation to the original author at Phoenixheart.</p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
            </div>
            <div class="clear"></div>
        </div>', 
        $options['debug'] ? 'checked="checked"' : '',
        $options['powered'] ? 'checked="checked"' : '',
        wp_create_nonce($this->config['plugin_name']));
    }
	
	function reset()
	{
	}
	
	function save()
	{
        $options = array(
            'debug' => isset($_POST['debug']),
            'powered' => isset($_POST['powered']),
        );
        
        get_option($this->config['plugin_name']) === false ? 
            add_option($this->config['plugin_name'], $options) : 
            update_option($this->config['plugin_name'], $options);
        
        echo '<p>Options saved</p>';
	}
}
