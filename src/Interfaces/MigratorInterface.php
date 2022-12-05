<?php
namespace Puleeno\WpMongo\Metadata\Interfaces;

interface MigratorInterface
{
    /**
     * @return \Puleeno\WpMongo\Metadata\Objects\MetaObject[]
     */
    public function getUnsyncedMetadata();


    /**
     * @param \Puleeno\WpMongo\Metadata\Objects\MetaObject
     *
     * @return boolean
     */
    public function migrate($metaobject);
}
