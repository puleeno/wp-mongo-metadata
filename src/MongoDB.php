<?php
namespace Puleeno\WpMongo\Metadata;

use MongoDB\Client;

class MongoDB
{
    protected static $instance;

    /**
     * @var \MongoDB\Client
     */
    protected $mongoDBClient;

    /**
     * @var string
     */
    protected $dbname;

    protected function __construct()
    {
        if (!(defined('MONGO_DB_HOST') && defined('MONGO_DB_NAME'))) {
            return;
        }

        $host = constant('MONGO_DB_HOST');
        $dbname = constant('MONGO_DB_NAME');

        $this->dbname = $dbname;

        $user = defined('MONGO_DB_USER') ? constant('MONGO_DB_USER') : null;
        $pwd = defined('MONGO_DB_PASSWORD') ? constant('MONGO_DB_PASSWORD') : null;

        $connectString = 'mongodb://';

        if ($user) {
            $connectString .= $user;

            if ($pwd) {
                $connectString .= ':' . $pwd;
            }
            $connectString .= '@';
        }
        $connectString .= $host;

        $this->mongoDBClient = new Client($connectString);
    }

    public static function getInstance()
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * @return \MongoDB\Client
     */
    public function getMongoClient()
    {
        if (is_null(static::$instance)) {
            static::getInstance();
        }
        return $this->mongoDBClient;
    }

    /**
     * @return \MongoDB\Database
     */
    public function getDatabase()
    {
        $client = $this->getMongoClient();
        if (is_null($client)) {
            return $client;
        }

        $dbname = $this->dbname;
        return $client->$dbname;
    }
}
