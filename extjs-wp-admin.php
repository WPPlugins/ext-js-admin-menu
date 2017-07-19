<?php
/*
Plugin Name: Ext JS Admin Menu
Plugin URI: http://extjswordpress.net/download/
Description: Ext JS Admin Menu
Author: W.Regenczuk
Version: 0.1
Author URI: http://extjswordpress.net/
*/ 

define('EXTJS_ADMIN_MENU_PLUGIN_URL', get_settings('siteurl').'/wp-content/plugins/extjs-admin-menu/');

function wp_admin_ext_css() {
echo  <<<CSS
<style>
.x-menu *{margin:0;padding:0;}
.x-menu ol, .x-menu ul {list-style:none;}
.x-menu-item { border-bottom:0px; }
#adminmenu {padding:0;margin:0;display:none;}
#submenu{display:none;}
#minisub{display:none;}
</style>
CSS;
echo '<link rel="stylesheet" type="text/css" href="' . EXTJS_ADMIN_MENU_PLUGIN_URL . 'ext/resources/css/ext.css" />'."\n\r";
}
function wp_admin_ext_js() {

if (!extension_loaded('json')) {
    require_once('JSON.php');
    $json = new HTML_AJAX_JSON();
    $json_menu = $json->encode(array_values(json_menu()));
} else {
    $json_menu = json_encode(array_values(json_menu()));
}


echo '<script type="text/javascript" src="' . EXTJS_ADMIN_MENU_PLUGIN_URL . 'ext/ext-base.js"></script>'."\n\r";
echo '<script type="text/javascript" src="' . EXTJS_ADMIN_MENU_PLUGIN_URL . 'ext/ext.js"></script>'."\n\r";
echo '<script type="text/javascript">'."\n\r";
print <<<JS
Ext.onReady(function(){
    adminmenuJSON= {$json_menu};
    Ext.get('adminmenu').update('<div id="ext_toolbar"></div>');
    Ext.get('adminmenu').show();
    var etb = new Ext.Toolbar('ext_toolbar');
    Ext.each(adminmenuJSON, function (e) {
        if (!e.menu) {
            etb.add({
                'text':e.text,
                'handler': function() { location.href = e.href; }
            });
        } else 
            etb.add(e);
    });
});
JS;
    echo '</script>';
}
add_action('admin_head', 'wp_admin_ext_css');
add_action('admin_footer', 'wp_admin_ext_js');





function json_menu() {
    $self = preg_replace('|^.*/wp-admin/|i', '', $_SERVER['PHP_SELF']);
    $self = preg_replace('|^.*/plugins/|i', '', $self);
    
  	global $menu, $submenu, $plugin_page, $pagenow;
        
    // Menu
    foreach ($menu as $item) {
    	if ( !empty($submenu[$item[2]]) ) {
            $submenu[$item[2]] = array_values($submenu[$item[2]]);  // Re-index.
    		$menu_hook = get_plugin_page_hook($submenu[$item[2]][0][2], $item[2]);
    		if ( file_exists(ABSPATH . PLUGINDIR . "/{$submenu[$item[2]][0][2]}") || !empty($menu_hook))
    			$am[$item[2]] = array('href' => 'admin.php?page='.$submenu[$item[2]][0][2], 'text' => $item[0]);
    		else
    			$am[$item[2]] = array('href' => $submenu[$item[2]][0][2], 'text' => $item[0]);
    	} else if ( current_user_can($item[1]) ) {
    		if ( file_exists(ABSPATH . PLUGINDIR . "/{$item[2]}") ) {
                $am[$item[2]] = array('href' => 'admin.php?page='.$item[2], 'text' => $item[0]);
    		} else {
                $am[$item[2]] = array('href' => $item[2], 'text' => $item[0]);
            } 
    	}
    }
    
    // Sub-menu
    unset($item);
    foreach ($submenu as $key => $val) {
        foreach ($val as $item) {
            if ( array_key_exists($key,$am) && current_user_can($item[1]) ) {
				$menu_hook = get_plugin_page_hook($item[2], $key);
                if (file_exists(ABSPATH . PLUGINDIR . "/".$item[2]) || ! empty($menu_hook)) {
                    $x = explode('?',$am[$key]['href']);
               		$am[$key]['menu'][] = array( 'href' => $x[0].'?page='.$item[2], 'text' => $item[0]);
                } else {
                    $am[$key]['menu'][] = array( 'href' => $item[2], 'text' => $item[0]);;
                }
            }
        }
    }
    return $am;
}
?>