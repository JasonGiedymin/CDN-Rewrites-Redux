<?php
class CDN_Rewrites
{
	var $profile_obj;
	var $option_obj;
	var $document;
	var $debug_result = array();
	var $config;
	
	function __construct()
	{		
		global $cdn_rewrites_config;
		$this->config = $cdn_rewrites_config;
		
		$this->profile_obj = CDN_Rewrites::get_instance('CDN_Rewrites_Profile');
		$this->option_obj = CDN_Rewrites::get_instance('CDN_Rewrites_Option');
	}
	
	function get_instance($class)
	{
		static $instances = array();

	        if (!array_key_exists($class, $instances))
        	{
	            $instances[$class] =& new $class;
        	}

	        $instance =& $instances[$class];

        	return $instance;
	}

	function currentPageURL() {
                $pageURL = 'http';

                if ($_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
                $pageURL .= "://";

                $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];

                return $pageURL;
        }

        function excludeThis( &$currentURL ) {
                $excludeCollection = array("register", "sign-up");

                if( is_array( $excludeCollection ) )
                {
                        foreach ( $excludeCollection as $key => $value )
                        {
                                $searchPos = strrpos($currentURL, $value);

                                if ( !empty($searchPos) ) {
                                        return true;
                                }
                        }

                }

                return false;
        }
	
	/**
	* @desc 
	*/
	function pre_content()
	{
		ob_start();
	}
	
	/**
	* @desc 
	*/
	function post_content()
        {
                $currentURL = $this->currentPageURL();

                if ( !$this->excludeThis($currentURL) ) {
                        $html = ob_get_contents();
                        ob_end_clean();
                        echo $this->parse($html);
                }
        }

	/**
	* @desc 
	*/
	function register_menu()
	{
		add_options_page(
			__('CDN Rewrites Redux', ''),
			__('CDN Rewrites Redux', ''),
			8,
			basename(__FILE__),
			array($this, 'build_options_form')
		);
	}
	
	/**
	* @desc 
	*/
	function build_options_form()
	{
		printf('
		<link rel="stylesheet" type="text/css" href="/wp-content/plugins/cdn-rewrites/css/start/jquery-ui-1.7.2.custom.css" />
        <link rel="stylesheet" type="text/css" href="/wp-content/plugins/cdn-rewrites/css/admin.css" />
        <div id="cdnr_dialog" title="Message" style="display:none"></div>
		<div class="wrap">
			<div id="icon-options-general" class="icon32"><br /></div>
			<h2>CDN Rewrites Redux Options</h2>
			<div id="message" class="updated fade" style="display:none"></div>
            <div id="generalLoading" class="updated fade" style="display:none">
                <p><img src="%s/images/loading.gif" alt="Loading..." /></p>
            </div>
			<div id="jquery-tabs">
				<ul>
					<li><a href="#listDiv">Profiles</a></li>
					<li><a href="#newDiv">New Profile</a></li>
					<li><a href="#editDiv">Edit Profile</a></li>
					<li><a href="#otherDiv">Other Options</a></li>
				</ul>', $this->config['plugin_path']);
		
		echo '<div id="listDiv">
		<table class="widefat" id="profilesTable">
			<thead>
				<tr>
					<th>Name</th>
                    <th>Rewrite Rule</th>
					<th>Applies To</th>
					<th style="width: 200px; text-align: right">Action</th>
				</tr>
			</thead>
			<tbody>
		';
		
		$profiles = $this->profile_obj->get_all();
		
		if (is_array($profiles))
		{
			foreach ($profiles as $p)
			{
				echo $this->profile_obj->format_html($p->id, $p->name, $p->origin, $p->dest, $p->apply, $p->enabled);
			}
		}
		
		echo '
				</tbody>
			</table>
		</div>' . $this->profile_obj->new_entry_html() . '
		
		
		<div id="editDiv">
		    <p id="editInstruction">Choose one of the profile to edit.</p>
		</div>' . $this->option_obj->panel() . '
		
		</div>
	</div>';
	}

	/**
	* @desc 
	*/
	function parse($html)
	{   
        $active_profiles = $this->profile_obj->get_active();
        if (empty($active_profiles))
        {
            return $html;
        }
        
		require_once(dirname(__FILE__) . '/simple_html_dom.php');
		$this->document = str_get_html($html);
       
        $css_tags = $this->document->find("link[rel='stylesheet']");
        $js_tags = $this->document->find('script[src]');
        $img_tags = $this->document->find('img');
        $embed_tags = array(
            'param' => $this->document->find("param[name='filename']"),
            'embed' => $this->document->find('embed[src]'),
        );
             
        foreach ($active_profiles as $p)
        {
            // CDN_Rewrites_Helper::log("\n\nApplying profile {$p->name}: {$p->origin} to {$p->dest}");
            
            $p->apply = explode(',', $p->apply);
            $p->excludes = unserialize($p->excludes);
            // CDN_Rewrites_Helper::log($p->excludes);
            
            // external stylesheets
            if (in_array('css', $p->apply))
            {
                foreach ($css_tags as $css)
                {
                    if (!$this->is_excluded($css->href, $p->excludes))
                    {
                        $css->href = $this->rewrite_host($css->href, $p->origin, $p->dest);
                    }
                    
                    // we don't need to parse CSS because most images in css are relative
                    // so if the css file in on CDN then the images are on CDN too
                }
            }
            
            if (in_array('js', $p->apply))
            {
                foreach ($js_tags as $js)
                {
                    if (!$this->is_excluded($js->src, $p->excludes))
                    {
                        $js->src = $this->rewrite_host($js->src, $p->origin, $p->dest);
                    }
                }
            }
            
            // images
            if (in_array('img', $p->apply))
            {
                foreach ($img_tags as $img)
                {
                    if (!$this->is_excluded($img->src, $p->excludes))
                    {
                        $img->src = $this->rewrite_host($img->src, $p->origin, $p->dest);
                    }
                }
            }
            
            // embeded
            if (in_array('embed', $p->apply))
            {
                // 1. <object> tag
                foreach ($embed_tags['param'] as $param)
                {
                    if (!$this->is_excluded($param->value, $p->excludes))
                    {
                        $param->value = $this->rewrite_host($param->value, $p->origin, $p->dest);
                    }
                }
                
                // 2. embed tag
                foreach ($embed_tags['embed'] as $embed)
                {
                    if (!$this->is_excluded($embed->src, $p->excludes))
                    {
                        $embed->src = $this->rewrite_host($embed->src, $p->origin, $p->dest);
                    }
                }
            }
            
            $html = $this->document->save();
            
            // background images
            if (in_array('bgr', $p->apply))
            {
                $regex = '/style\s*=\s*(?:".*?\s*\(\s*\'(.*?)\'\s*\).*?")/si';
                
                preg_match_all($regex, $html, $matches);
                
                foreach ($matches[1] as $url)
                {
                    if (!$this->is_excluded($url, $p->excludes))
                    {
                        $html = str_replace($url, $this->rewrite_host($url, $p->origin, $p->dest), $html);
                    }
                }
            }
        }
        
		
        if ($this->debug_result)
        {
            $html .= PHP_EOL . '<!-- START CDN REWRITES PLUGIN DEBUG DATA' . PHP_EOL;
            foreach ($this->debug_result as $origin => $rewritten)
            {
                $html .= "$origin
will be written into
$rewritten
----------------------------------------
";
            }
            $html .= sprintf('TOTAL: %s URL\'s to be rewritten', count($this->debug_result));
            $html .= PHP_EOL . 'END CDN REWRITES PLUGIN DEBUG DATA -->' . PHP_EOL;
        }
        
        return $html;
    }
    
    function rewrite_host($url, $origin, $dest)
    {
        if (is_array($url))
        {
            // some recursions
            $ret = array();
            foreach ($url as $single_url)
            {
                $ret[] = $this->rewrite_host($single_url, $origin, $dest);
            }
            return $ret;
        }
        
        if (strpos($url, $origin) !== 0) return $url;
        
        $rewritten_url = CDN_Rewrites_Helper::str_replace_once($origin, $dest, $url);
        
        $options = $this->option_obj->get();
        if ($options['debug'])
        {
            // if we are in debug mode, just show the changes, just collect
            $this->debug_result[$url] = $rewritten_url;
            // don't apply it
            return $url;
        }
        
        // CDN_Rewrites_Helper::log("$url written into $rewritten_url");
        
        return $rewritten_url;
    }
    
    

    /**
    * @desc 
    */
	function is_excluded($url, $excludes)
	{
        if (!is_array($excludes)) return false;
        
		foreach ($excludes as $base)
		{
			if (trim($base) == '') continue;
			if (strpos($url, $base) === 0) return true;
		}
		
		return false;
	}
	
	function is_included($abs_url)
	{
		foreach ($this->options['included'] as $base)
		{
			if (strpos($abs_url, $base) === 0) return true;
		}
		
		return false;
	}
	
	function get_current_url() 
	{
		static $current_url;
		if (isset($current_url)) return $current_url;
		
		$current_url = $this->get_current_host();

		if ($_SERVER['SERVER_PORT'] != 80) 
		{
			$current_url .=  $_SERVER['SERVER_PORT'];
		} 
		
		$current_url .= $_SERVER['REQUEST_URI'];

		return $current_url;
	}
	
	function get_current_host()
	{
		static $current_host;
		if (isset($current_host)) return $current_host;
		
		$current_host = (empty($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] != 443) ? 'http://' : 'https://';
		$current_host .= $_SERVER['SERVER_NAME'];
		return $current_host;
	}
	
	function activate()
	{
		$this->profile_obj->create_table();          
	}
	
	function deactivate()
	{
	}
	
	function wp_init()
	{
		if (is_admin())
		{
			wp_enqueue_script('fcdn_jquery_132', $this->config['plugin_path'] . 'js/jquery-1.3.2.min.js');
			wp_enqueue_script('fcdn_jquery_ui', $this->config['plugin_path'] . 'js/jquery-ui-1.7.2.custom.min.js');
			wp_enqueue_script('fcdn_admin_onload', $this->config['plugin_path'] . 'js/admin.js');
		}
		
		if (!isset($_POST['cdnr_action'])) return false;
		
		if (!wp_verify_nonce($_POST['_nonce'], $this->config['plugin_name']))
		{
			die('Security check failed. Please try refreshing.');
		}
		
		switch ($_POST['cdnr_action'])
		{
            case 'new_profile':
                $this->profile_obj->add();
                break;
            case 'activate':
                $this->profile_obj->activate();
                break;
            case 'deactivate':
                $this->profile_obj->activate(0);
                break;
            case 'delete':
                $this->profile_obj->delete();
                break;
            case 'edit':
                $this->profile_obj->edit();
                break;
            case 'update':
                $this->profile_obj->edit(true);
                break;
			case 'save_options':
                $this->option_obj->save();
                break;
			default:
				die('Invalid action.');
		}
		
		exit();
	}
    
    function show_powered()
    {
        $options = $this->option_obj->get();
        if ($options['powered'])
        {
            echo '<p class="cdnr_powered" style="text-align: center;"><small>Powered by <a href="http://www.amuxbit.com/projects/cdn-rewrites-redux/">CDN Rewrites Redux</a></small>&nbsp;<small> Based on <a href="http://www.phoenixheart.net/wp-plugins/cdn-rewrites/">CDN Rewrites Redux</a></small></p>';
        }
    }
    
    function add_setting_link($links)
    {
        $links[] = "<a href='options-general.php?page=cdnr.class.php'><b>Settings</b></a>";
        return $links;
    }
}
