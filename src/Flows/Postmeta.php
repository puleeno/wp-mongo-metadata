<?php
namespace Puleeno\WpMongo\Metadata\Flows;

use WP_Post;
use Puleeno\WpMongo\Metadata\MongoDB;
use Puleeno\WpMongo\Metadata\Objects\MetaObject;

class Postmeta
{
    protected $documents = [];
    protected $cachedPostMetas = [];
    protected $ignoreKeys = ['_wp_trash_meta_status', '_wp_desired_post_slug', '_wp_trash_meta_time'];

    public function __construct()
    {
        add_filter('get_post_metadata', [$this, 'getCustomPostMeta'], 5, 4);

        add_filter('add_post_metadata', [$this, 'addPostMeta'], 5, 5);
        add_filter('update_post_metadata', [$this, 'updatePostMeta'], 5, 5);

        add_filter('delete_post', [$this, 'deletePostMetas']);
    }

    public function getCustomPostMeta($value, $object_id, $meta_key, $single)
    {

    }


    public function addPostMeta($value, $object_id, $meta_key, $meta_value, $unique)
    {
    }

    /**
     * @return string
     */
    protected function insertPostMeta($collection, $meta_key, $meta_value, $post_id = null)
    {

    }

    public function updatePostMeta($value, $object_id, $meta_key, $meta_value, $prev_value)
    {
    }

    public function deletePostMetas($post_id)
    {
        $post     = get_post($post_id);
        if (is_null($post) || $post->meta_object_id) {
        }
        $database = MongoDB::getInstance()->getDatabase();
        $collection = $database->postmetas;

        // Delete data in Mongo DB
        $collection->deleteOne([
            '_id' => $post->meta_object_id
        ]);
        $collection->deleteMany([
            '_post_id' => $post_id,
        ]);
    }
}
