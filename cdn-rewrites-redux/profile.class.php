<?php
class CDN_Rewrites_Profile
{
	var $config;
	
	function CDN_Rewrites_Profile()
	{
		$this->__construct();
	}

	function __construct()
	{
		global $cdn_rewrites_config;
		$this->config = $cdn_rewrites_config;
	}
	
	function create_table()
	{
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		
		if ($wpdb->get_var("SHOW TABLES LIKE '{$this->config['profiles_table_name']}'") != $this->config['profiles_table_name'])
		{
			$q = "CREATE TABLE {$this->config['profiles_table_name']} (
			  `id` smallint(5) UNSIGNED NOT NULL AUTO_INCREMENT,
			  `name` varchar(50) NOT NULL,
              `origin` varchar(255) NOT NULL,
              `dest` varchar(255) NOT NULL,
			  `apply` varchar(255) NOT NULL,
			  `excludes` text NOT NULL,
			  `enabled` tinyint(4) NOT NULL default '1',
			  PRIMARY KEY (`id`)
			);";
			
			// CDN_Rewrites_Helper::log($q);
			
			dbDelta($q);
		}
	}
	
	function get_all()
	{
		global $wpdb;
		static $profiles;
		if (!isset($profiles))
		{
			$sql = "SELECT * FROM {$this->config['profiles_table_name']} ORDER BY `id` DESC";

			$profiles = $wpdb->get_results($sql);
		}
		return $profiles;
	}
    
    function get_active()
    {
        global $wpdb;
        static $active_profiles;
        if (!isset($active_profiles))
        {
            $sql = "SELECT * FROM {$this->config['profiles_table_name']} WHERE `enabled` = 1 ORDER BY `id` DESC";
            $active_profiles = $wpdb->get_results($sql);
        }
        return $active_profiles;
    }
	
	function format_html($id, $name, $origin, $dest, $apply, $enabled)
	{
        $desc = '<ul>';
        $apply = explode(',', $apply);
        foreach ($apply as $code)
        {
            $desc .= '<li>' . $this->apply_code_to_text($code) . '</li>';
        }
        $desc .= '</ul>';
        
		return sprintf(
            '<tr class="%s" id="profile_%s">
                <td>%s</td>
                <td>Rewrite <code>%s</code><br /> into <code>%s</code></td>
                <td>%s</td>
                <td style="text-align: right">
                    <a href="#" profile="%s" class="ajax" action="%s" >%s</a> |
                    <a href="#" profile="%s" class="ajax" action="edit">Edit</a> |
                    <a href="#" profile="%s" class="ajax need_confirm" confirmtext="Delete this profile?" action="delete">Delete</a>
                    <span id="loading_%s" style="display:none"><img src="%s/images/loading.gif" alt="Loading..." /></span>
                </td>
            </tr>', 
            $enabled ? 'active' : 'inactive', $id,
            stripslashes($name),
            $origin, $dest,
            $desc,
            $id, $enabled ? 'deactivate' : 'activate', $enabled ? 'Deactivate' : 'Activate',
            $id,
            $id,
            $id, $this->config['plugin_path']
        );
	}
    
    function apply_code_to_text($code)
    {
        switch ($code)
        {
            case 'css':
                return 'External CSS includes';
            case 'js':
                return 'External JavaScript includes';
            case 'img':
                return 'Images';
            case 'bgr':
                return 'Inline background images';
            case 'embed':
                return 'Embeded media contents';
            default:
                return '';
        }
    }
	
	function new_entry_html()
	{
		return sprintf(
		'<div id="newDiv">
			<form action="index.php" method="post" class="cdnr_ajax" autocomplete="off" ftype="add">
                <label for="name">Give this profile a name</label><br />
                <input type="text" name="name" style="width: 250px" id="name" />
                <h3>Rewrite Rule</h3>
                %s
				Rewrite static contents URL\'s that start with<br />
                <input style="width: 250px;" type="text" name="origin" /><br />
				into this host<br />
                <input style="width: 250px;" type="text" name="dest" />
				<h3>Apply the above rule on these contents</h3>
				<ul>
					<li><input type="checkbox" name="apply[css]" id="applyCss"><label for="applyCss">External CSS includes (<code>&lt;link rel=&quot;stylesheet&quot; tyle=&quot;text/css&quot; href=&quot;<strong>link/to/style.css</strong>&quot; /&gt;</code>)</label></li>
					<li><input type="checkbox" name="apply[js]" id="applyJS"><label for="applyJS">External JavaScript includes (<code>&lt;script type=&quot;text/javascript&quot; src=&quot;<strong>link/to/javascript.js</strong>&quot;&gt;&lt;/script&gt;</code>)</label></li>
					<li><input type="checkbox" name="apply[img]" id="applyImg"><label for="applyImg">Images (<code>&lt;img alt=&quot;alternate text&quot; src=&quot;<strong>link/to/photo.jpg</strong>&quot; /&gt;</code>)</label></li>
					<li><input type="checkbox" name="apply[bgr]" id="applyBgr"><label for="applyBgr">Inline background images (<code>&lt;div style=&quot;background:url(\'<strong>link/to/background.png</strong>\') top left repeat-y&quot;&gt;&lt;/div&gt;</code>)</label></li>
					<li><input type="checkbox" name="apply[embed]" id="applyEmbed"><label for="applyEmbed">Embeded contents (<code>&lt;param filename=&quot;<strong>link/to/track.mp3</strong>&quot; /&gt;</code> and <code>&lt;object src=&quot;<strong>link/to/flash.swf</strong>&quot; /&gt;</code>)</label></li>
                    <li>
                        <label for="excludes">Exclude these URL\'s or those start with one of them (one per line)</label><br />
                        <textarea name="excludes" id="excludes" style="width: 600px; height: 100px"></textarea>
                    </li>
                    <li><input type="checkbox" name="enabled" id="enabled" checked="checked"><label for="enabled">This profile is Active</label></li>
				 </ul>
				 <input type="hidden" name="_nonce" value="%s" />
				 <input type="hidden" name="cdnr_action" value="new_profile" />
				 <p class="submit">
					<input class="button-primary" type="submit" value="Add This Profile" name="submit"/>
                    
				 </p>
			</form>
		</div>', CDN_Rewrites_Helper::rule_instructs(), wp_create_nonce($this->config['plugin_name']));
	}
    
    function add()
    {
        $msg = $this->validate();
        if (!empty($msg)) CDN_Rewrites_Helper::ajax_error('<p>Profile NOT saved.</p><ul><li>' . implode('</li><li>', $msg) . '</li></ul>');
        
        list($name, $origin, $dest, $apply, $excludes, $enabled) = $this->parse_post_data();
        
        global $wpdb;
        
        $sql = $wpdb->prepare("INSERT INTO `{$this->config['profiles_table_name']}`(`name`, `origin`, `dest`, `apply`, `excludes`, `enabled`) 
                VALUES(%s, %s, %s, %s, %s, %d)",
                $name, $origin, $dest, $apply, $excludes, $enabled);
        
        // CDN_Rewrites_Helper::log($sql);

        $wpdb->query($sql);
        echo $this->format_html($wpdb->insert_id, $name, $origin, $dest, $apply, $enabled);
    }
    
    function activate($is_activating = 1)
    {
        global $wpdb;
        $id = intval($_POST['id']);
        $sql = "UPDATE `{$this->config['profiles_table_name']}` SET `enabled`=$is_activating WHERE `id`=$id";
        $wpdb->query($sql);
        echo $is_activating ? 'Deactivate' : 'Activate';
    }
    
    function delete()
    {
        global $wpdb;
        $id = intval($_POST['id']);
        $sql = "DELETE FROM `{$this->config['profiles_table_name']}` WHERE `id`=$id LIMIT 1";
        $wpdb->query($sql);
    }
    
    function edit($is_updating = false)
    {
        global $wpdb;
        $id = intval($_POST['id']);
        
        if (!$is_updating)
        {
            $sql = "SELECT * FROM `{$this->config['profiles_table_name']}` WHERE `id`=$id";
            $p = $wpdb->get_row($sql);
            if (empty($p))
            {
                CDN_Rewrites_Helper::ajax_error();
            }
            
            $p->apply = explode(',', $p->apply);
            $p->excludes = unserialize($p->excludes);
            
            printf(
            '
            <form action="index.php" method="post" class="cdnr_ajax" autocomplete="off" ftype="edit">
                <label for="nameE">Give this profile a name</label><br />
                <input type="text" name="name" style="width: 250px" id="nameE" value="%s" />
                <h3>Rewrite Rule</h3>
                %s
                Rewrite static contents URL\'s that start with<br />
                <input style="width: 250px;" type="text" name="origin" value="%s" /><br />
                into this host<br />
                <input style="width: 250px;" type="text" name="dest" value="%s" />
                <h3>Apply the above rule on these contents</h3>
                <ul>
                    <li><input %s type="checkbox" name="apply[css]" id="applyCssE"><label for="applyCssE">External CSS includes (<code>&lt;link rel=&quot;stylesheet&quot; tyle=&quot;text/css&quot; href=&quot;<strong>link/to/style.css</strong>&quot; /&gt;</code>)</label></li>
                    <li><input %s type="checkbox" name="apply[js]" id="applyJSE"><label for="applyJSE">External JavaScript includes (<code>&lt;script type=&quot;text/javascript&quot; src=&quot;<strong>link/to/javascript.js</strong>&quot;&gt;&lt;/script&gt;</code>)</label></li>
                    <li><input %s type="checkbox" name="apply[img]" id="applyImgE"><label for="applyImgE">Images (<code>&lt;img alt=&quot;alternate text&quot; src=&quot;<strong>link/to/photo.jpg</strong>&quot; /&gt;</code>)</label></li>
                    <li><input %s type="checkbox" name="apply[bgr]" id="applyBgrE"><label for="applyBgrE">Inline background images (<code>&lt;div style=&quot;background:url(\'<strong>link/to/background.png</strong>\') top left repeat-y&quot;&gt;&lt;/div&gt;</code>)</label></li>
                    <li><input %s type="checkbox" name="apply[embed]" id="applyEmbedE"><label for="applyEmbedE">Embeded contents (<code>&lt;param filename=&quot;<strong>link/to/track.mp3</strong>&quot; /&gt;</code> and <code>&lt;object src=&quot;<strong>link/to/flash.swf</strong>&quot; /&gt;</code>)</label></li>
                    <li>
                        <label for="excludesE">Exclude these URL\'s or those start with one of them (one per line)</label><br />
                        <textarea name="excludes" id="excludesE" style="width: 600px; height: 100px">%s</textarea>
                    </li>
                    <li><input %s type="checkbox" name="enabled" id="enabledE"><label for="enabledE">This profile is Active</label></li>
                 </ul>
                 <input type="hidden" name="id" value="%s" />
                 <input type="hidden" name="_nonce" value="%s" />
                 <input type="hidden" name="cdnr_action" value="update" />
                 <p class="submit">
                    <input class="button-primary" type="submit" value="Save Changes" name="submit"/>
                    <input class="button-secondary" type="button" value="Cancel" name="cancel"/>
                 </p>
            </form>
            ', 
            addslashes(stripslashes($p->name)),
            CDN_Rewrites_Helper::rule_instructs(),
            $p->origin,
            $p->dest,
            in_array('css', $p->apply) ? 'checked="checked"' : '',
            in_array('js', $p->apply) ? 'checked="checked"' : '',
            in_array('img', $p->apply) ? 'checked="checked"' : '',
            in_array('bgr', $p->apply) ? 'checked="checked"' : '',
            in_array('embed', $p->apply) ? 'checked="checked"' : '',
            is_array($p->excludes) ? implode("\n", $p->excludes) : '',
            $p->enabled ? 'checked="checked"' : '',
            $p->id,
            wp_create_nonce($this->config['plugin_name']));
            
            die();
        }
        
        $msg = $this->validate();
        if (!empty($msg)) CDN_Rewrites_Helper::ajax_error('<p>Profile NOT saved.</p><ul><li>' . implode('</li><li>', $msg) . '</li></ul>');
        
        list($name, $origin, $dest, $apply, $excludes, $enabled) = $this->parse_post_data();
        
        global $wpdb;
        
        $sql = $wpdb->prepare("UPDATE `{$this->config['profiles_table_name']}`
            SET `name`=%s, `origin`=%s, `dest`=%s, `apply`=%s, `excludes`=%s, `enabled`=%d
            WHERE `id`=%d",
            $name, $origin, $dest, $apply, $excludes, $enabled,
            $id);
                
        // CDN_Rewrites_Helper::log($sql);

        $wpdb->query($sql);
        echo $this->format_html($id, $name, $origin, $dest, $apply, $enabled);
    }
    
    function validate()
    {
        $msg = array();
        if (!isset($_POST['origin']) || trim($_POST['origin']) == '')
        {
            $msg[] = 'An origin host should be specified. A / (slash) specifies your current host.)';
        }
        if (!isset($_POST['dest']) || trim($_POST['dest']) == '')
        {
            $msg[] = 'A CDN host should be specified. Example: http://static.yoursite.com or http://images.yoursite.com.';
        }
        if (!isset($_POST['apply']) || !is_array($_POST['apply']) || empty($_POST['apply']))
        {
            $msg[] = 'At least one content type should be checked to apply this profile.';
        }
        
        return $msg;
    }
    
    function parse_post_data()
    {
        $origin = trim($_POST['origin']);
        if ($origin[strlen($origin) - 1] != '/')
        {
            $origin .= '/';
        }
        $dest = trim($_POST['dest']);
        if ($dest[strlen($dest) - 1] != '/')
        {
            $dest .= '/';
        }
        $name = trim($_POST['name']);
        
        $apply = array();
        foreach ($_POST['apply'] as $key => $val)
        {
            $apply[] = $key;
        }
        
        $apply = implode(',', $apply);
        $excludes = serialize(explode("\n", trim($_POST['excludes'])));
        $enabled = isset($_POST['enabled']);
        
        return array($name, $origin, $dest, $apply, $excludes, $enabled);
    }
}