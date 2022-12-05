<?php
namespace Puleeno\WpMongo\Metadata;

use Puleeno\WpMongo\Metadata\Interfaces\MigratorInterface;
use Puleeno\WpMongo\Metadata\Migrators\PostmetaMigrator;

class BatchManager
{
    protected $migrators = [];

    public function __construct()
    {
        $this->migrators = array_merge($this->migrators, [
            'post_meta' => new PostmetaMigrator(),
        ]);
    }

    public function run()
    {
        if (count($this->migrators) > 0) {
            foreach ($this->migrators as $key => $migrator) {
                if (!is_a($migrator, MigratorInterface::class)) {
                    continue;
                }
                $unsyncedMetas = $migrator->getUnsyncedMetadata();
                if (empty($unsyncedMetas)) {
                    continue;
                }
                foreach ($unsyncedMetas as $unsyncedMeta) {
                    $migrator->migrate($unsyncedMeta);
                }
            }
        }
    }
}
