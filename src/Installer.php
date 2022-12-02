<?php
namespace Puleeno\WpMongo\Metadata;

class Installer
{
    public static function active()
    {
        $db = MongoDB::getInstance()->getDatabase();

        $collections = iterator_to_array($db->listCollectionNames());

        // Create collections
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
}
