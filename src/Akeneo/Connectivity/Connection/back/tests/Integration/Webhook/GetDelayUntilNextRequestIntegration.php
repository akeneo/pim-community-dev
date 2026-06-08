<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\Integration\Webhook;

use Akeneo\Connectivity\Connection\Tests\CatalogBuilder\EventsApiRequestCountLoader;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

/**
 * MySQL 8.4 changed GREATEST(DATETIME, INT) — when the INT side originates from a nullable
 * DATETIME column (e.g. COALESCE(nullable_datetime_col, 0)) — to return DATETIME(6) instead
 * of DOUBLE. GetDelayUntilNextRequest::execute() parses the `updated` value using
 * createFromFormat('Y-m-d H:i:s', ...) which cannot handle a DATETIME(6) string such as
 * '2021-01-02 11:50:55.000000'; it returns false, and the subsequent getTimestamp() call
 * throws a TypeError.
 *
 * The production query does a plain SELECT of the `updated` DATETIME column, so MySQL 8.4
 * does not currently trigger DATETIME(6) output for that path. However, the parsing code
 * is fragile: any future use of GREATEST() against that column would cause the failure. This
 * test demonstrates the vulnerability by replicating the MySQL 8.4 DATETIME(6) output via
 * GREATEST(t1.updated, COALESCE(t2.updated, 0)) with a LEFT JOIN that always yields NULL on
 * the right side, then running the exact same parsing logic used in execute().
 *
 * The test skips on MySQL 8.0 (GREATEST returns a DOUBLE numeric string) and fails on
 * MySQL 8.4 before the parsing code is fixed.
 */
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

    public function testExecuteCanHandleUpdatedDatetime6FormatFromMysql84(): void
    {
        $this->eventsApiRequestCountLoader->createEventsApiRequestCount(
            new \DateTimeImmutable('2021-01-02 11:50:55', new \DateTimeZone('UTC')),
            50
        );

        // GREATEST(DATETIME_col, COALESCE(nullable_DATETIME_col, 0)) with the right side NULL:
        // - MySQL 8.0: promotes to DOUBLE, returns a numeric string like '20210102115055'
        // - MySQL 8.4: promotes to DATETIME(6), returns '2021-01-02 11:50:55.000000'
        // A literal COALESCE(NULL, 0) does NOT trigger this — the right side must be a typed
        // DATETIME column. Here we use a self-join with an impossible condition so t2 is always NULL.
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

        $updatedValue = (string) $row['updated'];

        // On MySQL 8.0 $updatedValue is '2021-01-02 11:50:55' (no microseconds) — passes.
        // On MySQL 8.4 $updatedValue is '2021-01-02 11:50:55.000000' — fails.
        // GetDelayUntilNextRequest::execute() calls createFromFormat('Y-m-d H:i:s', ...) on
        // the `updated` value and then calls getTimestamp() on the result. When createFromFormat
        // returns false (which it does for strings with trailing microseconds), getTimestamp()
        // throws a TypeError.
        $lastDateTime = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $updatedValue);

        Assert::assertInstanceOf(
            \DateTimeImmutable::class,
            $lastDateTime,
            sprintf(
                'GetDelayUntilNextRequest::execute() would fail with a TypeError on MySQL 8.4 '
                . 'because createFromFormat(\'Y-m-d H:i:s\', \'%s\') returns false.',
                $updatedValue
            )
        );
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
