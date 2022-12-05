<?php
namespace Puleeno\WpMongo\Metadata;

class Installer
{
    protected static function createObjectIdForPostsTable()
    {
        global $wpdb;
        if (!get_option('wp_mongo_metadata_create_object_key', false)) {
            $sql = "ALTER TABLE `{$wpdb->prefix}posts` ADD `meta_object_id` VARCHAR(32) NULL AFTER `ID`;";
            $wpdb->query($sql);

            $sql= "ALTER TABLE `{$wpdb->prefix}posts` ADD UNIQUE `post_meta_object_id` (`meta_object_id`(32));";
            $wpdb->query($sql);

            // Update flag to skip update table posts.
            update_option('wp_mongo_metadata_create_object_key', true);
        }
    }

    protected static function createMongoDbCollections()
    {
        $db          = MongoDB::getInstance()->getDatabase();
        $collections = iterator_to_array($db->listCollectionNames());

        if (array_search('postmetas', $collections, true) === false) {
            $db->createCollection('postmetas');
        }
        if (array_search('usermetas', $collections, true) === false) {
            $db->createCollection('usermetas');
        }
        if (array_search('termmetas', $collections, true) === false) {
            $db->createCollection('termmetas');
        }
    }

    protected static function setupBatchRunner()
    {
    }

    public static function active()
    {
        // Update WordPress DB
        static::createObjectIdForPostsTable();

        // Create Mongo collections
        static::createMongoDbCollections();

        // Init batch runner
        static::setupBatchRunner();
    }

    public static function deactive()
    {
    }
}
