<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\EventsApiRequestCountLoader;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class GetDelayUntilNextRequestIntegration extends TestCase
{
    private EventsApiRequestCountLoader $eventsApiRequestCountLoader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eventsApiRequestCountLoader = $this->get(
            'akeneo_connectivity.connection.fixtures.events_api_request_count_loader'
        );
    }

    /**
     * MySQL 8.4 changed GREATEST(DATETIME, COALESCE(nullable_DATETIME_col, 0)) to return
     * DATETIME(6) (e.g. '2021-01-02 11:50:55.000000') instead of a plain DATETIME string.
     * A literal COALESCE(NULL, 0) does NOT trigger this — the fallback must be a typed DATETIME
     * column; here we force that with a self-join on an impossible condition so t2 is always NULL.
     * GetDelayUntilNextRequest::execute() calls createFromFormat('Y-m-d H:i:s', ...) on the
     * `updated` value: when that returns false for a DATETIME(6) string, the subsequent
     * getTimestamp() call throws a TypeError.
     * Passes on MySQL 8.0 (no microseconds), fails on MySQL 8.4 before the fix.
     */
    public function testExecuteCanHandleUpdatedDatetime6FormatFromMysql84(): void
    {
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2021-01-02 11:50:55', new \DateTimeZone('UTC')),
            50
        );

        $row = $this->get('database_connection')->executeQuery(
            <<<SQL
            SELECT
                t1.event_count,
                GREATEST(t1.updated, COALESCE(t2.updated, 0)) AS updated
            FROM akeneo_connectivity_connection_events_api_request_count t1
            LEFT JOIN akeneo_connectivity_connection_events_api_request_count t2
                ON t2.event_minute = -1
            ORDER BY t1.updated DESC
            LIMIT 1
            SQL
        )->fetchAssociative();

        $this->assertIsArray($row);

        $lastDateTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', (string) $row['updated']);

        Assert::assertInstanceOf(
            \DateTimeImmutable::class,
            $lastDateTime,
            sprintf(
                'GetDelayUntilNextRequest::execute() would fail with a TypeError on MySQL 8.4 '
                . 'because createFromFormat(\'Y-m-d H:i:s\', \'%s\') returns false.',
                $row['updated']
            )
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
