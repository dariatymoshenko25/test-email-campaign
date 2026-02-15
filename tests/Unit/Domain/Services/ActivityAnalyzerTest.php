<?php

namespace EmailCampaign\Tests\Unit\Domain\Services;

use EmailCampaign\Domain\Services\ActivityAnalyzer;
use EmailCampaign\Domain\Interfaces\ActivityRepositoryInterface;
use EmailCampaign\Domain\Interfaces\LoggerInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class ActivityAnalyzerTest extends TestCase
{
    private $mongoRepository;
    private $mysqlRepository;
    private $logger;
    private $analyzer;

    protected function setUp(): void
    {
        $this->mongoRepository = Mockery::mock(ActivityRepositoryInterface::class);
        $this->mysqlRepository = Mockery::mock(ActivityRepositoryInterface::class);
        $this->logger = Mockery::mock(LoggerInterface::class);

        $this->analyzer = new ActivityAnalyzer(
            $this->mongoRepository,
            $this->mysqlRepository,
            $this->logger
        );
    }

    public function testUserActiveViaMongoDB()
    {
        $userId = 123;

        $this->mongoRepository->shouldReceive('hasActivityLast24h')
            ->with($userId)
            ->once()
            ->andReturn(true);

        $this->mysqlRepository->shouldNotReceive('hasActivityLast24h');

        $result = $this->analyzer->isUserActive($userId);

        $this->assertTrue($result);
    }

    public function testUserActiveViaMySQL()
    {
        $userId = 456;

        $this->mongoRepository->shouldReceive('hasActivityLast24h')
            ->with($userId)
            ->once()
            ->andReturn(false);

        $this->mysqlRepository->shouldReceive('hasActivityLast24h')
            ->with($userId)
            ->once()
            ->andReturn(true);

        $result = $this->analyzer->isUserActive($userId);

        $this->assertTrue($result);
    }

    public function testUserNotActive()
    {
        $userId = 789;

        $this->mongoRepository->shouldReceive('hasActivityLast24h')
            ->with($userId)
            ->once()
            ->andReturn(false);

        $this->mysqlRepository->shouldReceive('hasActivityLast24h')
            ->with($userId)
            ->once()
            ->andReturn(false);

        $result = $this->analyzer->isUserActive($userId);

        $this->assertFalse($result);
    }

    public function testErrorHandling()
    {
        $userId = 111;

        $this->mongoRepository->shouldReceive('hasActivityLast24h')
            ->with($userId)
            ->once()
            ->andThrow(new \Exception('Database error'));

        $this->logger->shouldReceive('error')->once();

        $result = $this->analyzer->isUserActive($userId);

        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }
}
