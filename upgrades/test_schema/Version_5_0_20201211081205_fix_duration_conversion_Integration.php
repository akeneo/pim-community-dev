<?php

declare(strict_types=1);

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

class Version_5_0_20201211081205_fix_duration_conversion_Integration extends TestCase
{
    private const MIGRATION_LABEL = '_5_0_20201211081205_fix_duration_conversion';

    use ExecuteMigrationTrait;

    private Connection $connection;

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = $this->get('database_connection');
    }

    /** @test */
    public function it_fixes_the_duration_conversion(): void
    {
        $this->setMonthConversionValue('12');
        self::assertEquals('12', $this->getMonthConversionValue());

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertEquals('2628000', $this->getMonthConversionValue());
    }

    private function getMonthConversionValue(): string
    {
        $query = "SELECT units FROM akeneo_measurement WHERE code = 'Duration';";
        $units = $this->connection->executeQuery($query)->fetchColumn();

        $monthUnit = current(array_filter(
            \json_decode($units, true),
            fn (array $unit): bool => $unit['code'] === 'MONTH'
        ));
        Assert::notNull($monthUnit);

        return $monthUnit['convert_from_standard'][0]['value'];
    }

    private function setMonthConversionValue(string $value): void
    {
        $query = "SELECT units FROM akeneo_measurement WHERE code = 'Duration';";
        $units = \json_decode($this->connection->executeQuery($query)->fetchColumn(), true);

        $newUnits = array_map(
            function (array $unit) use ($value): array {
                if ($unit['code'] === 'MONTH') {
                    $unit['convert_from_standard'][0]['value'] = $value;
                }

                return $unit;
            },
            $units
        );

        $updateQuery = "UPDATE akeneo_measurement SET units = :units WHERE code = 'Duration';";
        $this->connection->executeQuery($updateQuery, ['units' => \json_encode($newUnits)]);
    }
}
