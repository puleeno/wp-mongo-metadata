<?php
/**
 * Plugin Name: WP Mongo Metadata
 * Author: Puleeno Nguyen
 * Author URI: https://wp.puleeno.com
 */

use Puleeno\WpMongo\Metadata\MongoMetadataPlugin;

define('WP_MONGO_METATA_PLUGIN_FILE', __FILE__);


$composerAutoloader = dirname(__FILE__) . '/vendor/autoload.php';
if (file_exists($composerAutoloader)) {
    require_once $composerAutoloader;

    $GLOBALS['mongometadata'] = MongoMetadataPlugin::getInstance();
}
