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
        global $post;

        // Do not need re-query post if this post is proccessing
        $needPost = (is_a($post, WP_Post::class) && $post->ID === $object_id) ? $post : get_post($object_id);

        if ($needPost->meta_object_id) {
            return $this->resolveMeta($needPost->meta_object_id, $meta_key, $value, $single);
        }

        return $value;
    }

    /**
     *
     */
    public function resolveMeta($meta_object_id, $meta_key, $value, $single)
    {
        $document = isset($this->documents[$meta_object_id])
                ? $this->documents[$meta_object_id]
                : MongoDB::get_post_meta($meta_object_id, $value);
    }


    public function addPostMeta($value, $object_id, $meta_key, $meta_value, $unique)
    {
        if (in_array($meta_key, $this->ignoreKeys)) {
            return $value;
        }

        $post = get_post($object_id);
        $metaObject = new MetaObject($object_id);

        $database = MongoDB::getInstance()->getDatabase();
        $collection = $database->postmetas;

        // try get Mongo Object Id from caches
        $validMetaObjectId = isset($this->cachedPostMetas[$object_id]) ? $this->cachedPostMetas[$object_id] : $post->meta_object_id;

        if (is_null($validMetaObjectId)) {
            $validMetaObjectId = $this->insertPostMeta($collection, $meta_key, $meta_value, $post->ID);
            if (empty($validMetaObjectId)) {
                return $value;
            }

            $metaObject->setMetaObjectId($validMetaObjectId);
            $metaObject->sync();

            $this->cachedPostMetas[$object_id] = $validMetaObjectId;

            return $meta_value;
        } else {
            /**
             * @var \MongoDB\Model\BSONDocument
             */
            $document = $collection->findOne(['_id' => $validMetaObjectId]);
            $values   = [];

            if ($unique) {
                $values = [
                    $meta_value
                ];
            } elseif (!is_null($document)) {
                $values = $document->$meta_key;
                if (is_array($values)) {
                    array_unshift($values, $meta_value);
                } else {
                    $values = [$meta_value];
                }
            }

            $result = null;
            if (is_null($document)) {
                $insertOneResult = $collection->insertOne([
                    $meta_key => $values,
                ]);
                $result = is_null($insertOneResult->getInsertedId()) ? 0 : 1;
            } else {
                $updateResult = $collection->updateOne([
                    '_id' => $validMetaObjectId,
                ], [
                    '$set' => $document
                ]);
                $result = $updateResult->getModifiedCount();
            }
            if ($result > 0) {
                return $meta_value;
            }
        }
        return $value;
    }

    /**
     * @return string
     */
    protected function insertPostMeta($collection, $meta_key, $meta_value, $post_id = null)
    {
        $values = [
            $meta_key => [
                $meta_value,
            ]
        ];

        if (!empty($post_id)) {
            $values['_post_id'] = $post_id;
        }

        $insertOneResult = $collection->insertOne($values);

        return (string) $insertOneResult->getInsertedId();
    }

    public function updatePostMeta($value, $object_id, $meta_key, $meta_value, $prev_value)
    {
        if (in_array($meta_key, $this->ignoreKeys)) {
            return $value;
        }

        $post = get_post($object_id);
        $metaObject = new MetaObject($object_id);

        $database = MongoDB::getInstance()->getDatabase();
        $collection = $database->postmetas;

        // try get Mongo Object Id from caches
        $validMetaObjectId = isset($this->cachedPostMetas[$object_id]) ? $this->cachedPostMetas[$object_id] : $post->meta_object_id;

        if (is_null($validMetaObjectId)) {
            $validMetaObjectId = $this->insertPostMeta($collection, $meta_key, $meta_value, $post->ID);
            if (empty($validMetaObjectId)) {
                return $value;
            }

            $metaObject->setMetaObjectId($validMetaObjectId);
            $metaObject->sync();

            $this->cachedPostMetas[$object_id] = $validMetaObjectId;

            return $meta_value;
        } else {
            /**
             * @var \MongoDB\Model\BSONDocument
             */
            $document = $collection->findOne(['_id' => $validMetaObjectId]);

            if (!is_null($document)) {
                $document->$meta_key = [
                    $meta_value
                ];

                $updateResult = $collection->updateOne([
                    '_id' => $validMetaObjectId,
                ], [
                    '$set' => $document
                ]);

                if ($updateResult->getModifiedCount() > 0) {
                    return $meta_value;
                }
            }

            if (empty($this->insertPostMeta($collection, $meta_key, $meta_value, $object_id))) {
                return $meta_value;
            }
        }
        return $value;
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
