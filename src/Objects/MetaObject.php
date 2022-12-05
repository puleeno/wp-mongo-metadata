<?php
namespace Puleeno\WpMongo\Metadata\Objects;

class MetaObject
{
    protected $id;
    protected $metaObjectId;

    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        }
    }

    public function __construct($id, $metaObjectId = null)
    {
        $this->id = $id;

        if (!is_null($metaObjectId)) {
            $this->metaObjectId;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return self
     */
    public function setMetaObjectId($metaObjectId)
    {
        $this->metaObjectId = $metaObjectId;

        return $this;
    }

    public function getMetaObjectId()
    {
        return $this->metaObjectId;
    }

    /**
     * @return self
     */
    public static function parseFromObject($object)
    {
        return new self($object->id, $object->meta_object_id);
    }

    public function sync()
    {
        global $wpdb;

        if (empty($this->id) || empty($this->metaObjectId)) {
            return 0;
        }

        return $wpdb->update($wpdb->posts, [
            'meta_object_id' => (string) $this->metaObjectId,
        ], [
            'ID' => $this->id,
        ]);
    }
}
