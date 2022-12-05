<?php
namespace Puleeno\WpMongo\Metadata\Migrators;

use Exception;
use Puleeno\WpMongo\Metadata\Abstracts\MigratorAbstract;
use Puleeno\WpMongo\Metadata\Objects\MetaObject;
use Puleeno\WpMongo\Metadata\QueryManager;
use Puleeno\WpMongo\Metadata\MongoDB;

class PostmetaMigrator extends MigratorAbstract
{
    public function getUnsyncedMetadata()
    {
        $query = QueryManager::getInstance();
        $rows  = $query->getUnsyncedPostMetas();

        if (is_null($rows)) {
            return [];
        }
        return array_map([MetaObject::class, 'parseFromObject'], $rows);
    }

    /**
     * @param \Puleeno\WpMongo\Metadata\Objects\MetaObject $metaObject
     *
     * @return boolean
     */
    public function migrate($metaObject)
    {
        $updatedRows = 0;

        try {
            $database = MongoDB::getInstance()->getDatabase();

            $postMeta   = get_post_custom($metaObject->getId());
            $collection = $database->postmetas;

            $insertOneResult = $collection->insertOne($postMeta);
            $objectId        = $insertOneResult->getInsertedId();

            $metaObject->setMetaObjectId((string) $objectId);

            // Update meta object ID for WordPress posts
            $metaObject->sync();
        } catch (Exception $e) {
            error_log($e->getMessage());
            return false;
        }
        return $updatedRows > 0;
    }
}
