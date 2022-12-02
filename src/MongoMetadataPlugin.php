<?php
namespace Puleeno\WpMongo\Metadata;

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

        // Change flow get meta data of WordPress Core to WP Mongo Metadata
    }
}
