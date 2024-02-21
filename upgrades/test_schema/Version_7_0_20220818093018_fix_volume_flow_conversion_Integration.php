<?php

namespace Pim\Upgrade\Schema\Tests;

use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

class Version_7_0_20220818093018_fix_volume_flow_conversion_Integration extends TestCase
{
    private const MIGRATION_LABEL = '_7_0_20220818093018_fix_volume_flow_conversion';

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
    public function it_fixes_the_incorrect_volume_flow_conversion(): void
    {
        $this->setIncorrectConversions();

        self::assertEquals('mul', $this->getConversionOperatorFor('LITER_PER_MINUTE'));
        self::assertEquals('mul', $this->getConversionOperatorFor('LITER_PER_HOUR'));
        self::assertEquals('mul', $this->getConversionOperatorFor('LITER_PER_DAY'));
        self::assertEquals('mul', $this->getConversionOperatorFor('MILLILITER_PER_MINUTE'));
        self::assertEquals('mul', $this->getConversionOperatorFor('MILLILITER_PER_HOUR'));
        self::assertEquals('mul', $this->getConversionOperatorFor('MILLILITER_PER_DAY'));

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertEquals('div', $this->getConversionOperatorFor('LITER_PER_MINUTE'));
        self::assertEquals('div', $this->getConversionOperatorFor('LITER_PER_HOUR'));
        self::assertEquals('div', $this->getConversionOperatorFor('LITER_PER_DAY'));
        self::assertEquals('div', $this->getConversionOperatorFor('MILLILITER_PER_MINUTE'));
        self::assertEquals('div', $this->getConversionOperatorFor('MILLILITER_PER_HOUR'));
        self::assertEquals('div', $this->getConversionOperatorFor('MILLILITER_PER_DAY'));
    }

    /** @test */
    public function it_does_not_update_conversion_modified_by_user()
    {
        $this->updateConversionFor('LITER_PER_HOUR', [
            ['value' => '0.1', 'operator' => 'mul'],
            ['value' => '3600', 'operator' => 'mul'],
        ]);

        $this->reExecuteMigration(self::MIGRATION_LABEL);

        self::assertEquals('div', $this->getConversionOperatorFor('LITER_PER_MINUTE'));
        // Ensure we didn't update the conversion for LITER_PER_HOUR as it was modified by the user
        self::assertEquals('mul', $this->getConversionOperatorFor('LITER_PER_HOUR'));
    }

    private function getConversionOperatorFor(string $unitCode)
    {
        $query = "SELECT units FROM akeneo_measurement WHERE code = 'VolumeFlow';";
        $units = \json_decode($this->connection->executeQuery($query)->fetchOne(), true);
        $unit = current(array_filter(
            $units,
            static fn (array $unit): bool => $unit['code'] === $unitCode
        ));

        return $unit['convert_from_standard'][1]['operator'];
    }

    private function updateConversionFor(string $unitCode, array $conversion): void
    {
        $query = "SELECT units FROM akeneo_measurement WHERE code = 'VolumeFlow';";
        $units = \json_decode($this->connection->executeQuery($query)->fetchOne(), true);

        $newUnits = array_map(
            function (array $unit) use ($unitCode, $conversion): array {
                if ($unit['code'] === $unitCode) {
                    $unit['convert_from_standard'] = $conversion;
                }

                return $unit;
            },
            $units
        );

        $updateQuery = "UPDATE akeneo_measurement SET units = :units WHERE code = 'VolumeFlow';";
        $this->connection->executeQuery($updateQuery, ['units' => \json_encode($newUnits)]);
    }

    private function setIncorrectConversions(): void
    {
        $this->updateConversionFor('LITER_PER_MINUTE', [
            ['value' => '0.001', 'operator' => 'mul'],
            ['value' => '60', 'operator' => 'mul'],
        ]);

        $this->updateConversionFor('LITER_PER_HOUR', [
            ['value' => '0.001', 'operator' => 'mul'],
            ['value' => '3600', 'operator' => 'mul'],
        ]);

        $this->updateConversionFor('LITER_PER_DAY', [
            ['value' => '0.001', 'operator' => 'mul'],
            ['value' => '86400', 'operator' => 'mul'],
        ]);

        $this->updateConversionFor('MILLILITER_PER_MINUTE', [
            ['value' => '0.000001', 'operator' => 'mul'],
            ['value' => '60', 'operator' => 'mul'],
        ]);

        $this->updateConversionFor('MILLILITER_PER_HOUR', [
            ['value' => '0.000001', 'operator' => 'mul'],
            ['value' => '3600', 'operator' => 'mul'],
        ]);

        $this->updateConversionFor('MILLILITER_PER_DAY', [
            ['value' => '0.000001', 'operator' => 'mul'],
            ['value' => '86400', 'operator' => 'mul'],
        ]);
    }
}
