<?php

namespace EmailCampaign\Infrastructure\Database;

use EmailCampaign\Config\Config;
use MongoDB\Client;

class MongoConnection
{
    private Client $client;
    private string $databaseName;
    private static ?self $instance = null;

    private function __construct()
    {
        $config = Config::getInstance()->get('database.mongo');

        $this->client = new Client($config['uri']);
        $this->databaseName = $config['database'];
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getCollection(string $collectionName): \MongoDB\Collection
    {
        return $this->client->selectCollection($this->databaseName, $collectionName);
    }
}
