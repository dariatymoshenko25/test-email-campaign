<?php

namespace EmailCampaign\Infrastructure\Repositories;

use EmailCampaign\Domain\Entities\Activity;
use EmailCampaign\Domain\Interfaces\ActivityRepositoryInterface;
use EmailCampaign\Infrastructure\Database\MongoConnection;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;

class MongoActivityRepository implements ActivityRepositoryInterface
{
    private Collection $collection;
    private int $minProductViews;

    public function __construct(?MongoConnection $connection = null)
    {
        $config = \EmailCampaign\Config\Config::getInstance();
        $this->collection = ($connection ?? MongoConnection::getInstance())->getCollection('user_activity_logs');
        $this->minProductViews = $config->get('campaign.min_product_views', 3);
    }

    public function getUserActivityLast24h(int $userId): array
    {
        $twentyFourHoursAgo = new \DateTime('-24 hours');

        $cursor = $this->collection->find([
            'user_id' => $userId,
            'action' => 'view_product',
            'created_at' => [
                '$gte' => new UTCDateTime($twentyFourHoursAgo)
            ]
        ], [
            'sort' => ['created_at' => -1]
        ]);

        $activities = [];
        foreach ($cursor as $document) {
            $activities[] = new Activity(
                $document['user_id'],
                $document['action'],
                $this->convertMongoDate($document['created_at'])
            );
        }

        return $activities;
    }

    public function hasActivityLast24h(int $userId): bool
    {
        $count = $this->countActivityLast24h($userId);
        return $count >= $this->minProductViews;
    }

    public function countActivityLast24h(int $userId): int
    {
        $twentyFourHoursAgo = new \DateTime('-24 hours');

        return (int)$this->collection->countDocuments([
            'user_id' => $userId,
            'action' => 'view_product',
            'created_at' => [
                '$gte' => new UTCDateTime($twentyFourHoursAgo)
            ]
        ]);
    }

    private function convertMongoDate($mongoDate): \DateTime
    {
        if ($mongoDate instanceof UTCDateTime) {
            return $mongoDate->toDateTime();
        }

        return new \DateTime($mongoDate);
    }
}
