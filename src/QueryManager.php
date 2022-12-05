<?php

use League\Plates\Template\Name;

namespace Puleeno\WpMongo\Metadata;

class QueryManager
{
    /**
     * @var self
     */
    protected static $instance;

    /**
     * @var \wpdb
     */
    protected static $wpdb;

    protected function __construct()
    {
        static::$wpdb = &$GLOBALS['wpdb'];
    }

    /**
     * @return self
     */
    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return \Puleeno\WpMongo\Metadata\Objects\MetaObject[]
     */
    public function getUnsyncedPostMetas()
    {
        global $wpdb;

        $maxiumProcessRecords = apply_filters(
            'puleeno/wp/mongo/metadata/post/processing',
            50
        );

        return static::$wpdb->get_results(
            static::$wpdb->prepare(
                "SELECT {$wpdb->posts}.id, {$wpdb->posts}.meta_object_id FROM {$wpdb->posts} WHERE {$wpdb->posts}.meta_object_id IS NULL ORDER BY {$wpdb->posts}.post_date ASC LIMIT %d",
                $maxiumProcessRecords
            )
        );
    }
}
