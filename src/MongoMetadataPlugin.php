<?php
namespace Puleeno\WpMongo\Metadata;

use Puleeno\WpMongo\Metadata\Flows\Postmeta;

class MongoMetadataPlugin
{
    protected static $instance;

    protected function __construct()
    {
        $this->initHooks();
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }


    protected function initHooks()
    {
        register_activation_hook(WP_MONGO_METATA_PLUGIN_FILE, [Installer::class, 'active']);

        // Init CRON
        if (defined('DOING_CRON') && constant('DOING_CRON')) {
            $batchManager = new BatchManager();
            add_action('mongo_metadata_cron', array($batchManager, 'run'));

            if (defined('WP_MONGO_METADATA_CRON_DEBUG') && constant('WP_MONGO_METADATA_CRON_DEBUG') == true) {
                do_action('mongo_metadata_cron');
            }
        }

        // Register new schedules
        new Schedules();

        // Change flow get meta data of WordPress Core to WP Mongo Metadata
        new Postmeta();
    }
}
