<?php

namespace Akeneo\Test\Channel\Integration\Query\Sql;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

final class SqlFindChannelsIntegration extends TestCase
{
    private Connection $connection;
    private FindChannels $sqlFindChannels;

    public function setUp(): void
    {
        parent::setUp();

        $this->connection = $this->get('database_connection');
        $this->sqlFindChannels = $this->get(
            'Akeneo\Channel\Infrastructure\Query\Sql\SqlFindChannels'
        );
    }

    public function test_it_finds_all_channels(): void
    {
        $conversionUnits = ['a_measurement_attribute' => 'KILOGRAM'];
        $this->saveConversionUnitsForChannel($conversionUnits, 'print');

        $results = $this->sqlFindChannels->findAll();

        $this->assertIsArray($results);
        $this->assertCount(3, $results);
        $this->assertContainsOnlyInstancesOf(Channel::class, $results);

        $printChannel = current(array_filter($results, fn (Channel $channel) => $channel->getCode() === 'print'));

        $this->assertContains('de_DE', $printChannel->getLocaleCodes());
        $this->assertContains('en_US', $printChannel->getLocaleCodes());
        $this->assertContains('fr_FR', $printChannel->getLocaleCodes());

        $this->assertContains('USD', $printChannel->getActiveCurrencies());
        $this->assertContains('EUR', $printChannel->getActiveCurrencies());

        $this->assertEquals('Print', $printChannel->getLabels()->getLabel('en_US'));
        $this->assertEquals('Print', $printChannel->getLabels()->getLabel('fr_FR'));
        $this->assertEquals('Print', $printChannel->getLabels()->getLabel('de_DE'));
        $this->assertEquals(null, $printChannel->getLabels()->getLabel('jp_JP'));

        $this->assertEquals('KILOGRAM', $printChannel->getConversionUnits()->getConversionUnit('a_measurement_attribute'));
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }

    /**
     * @param array<string, string> $conversionUnits
     */
    private function saveConversionUnitsForChannel(array $conversionUnits, string $channelCode): void
    {
        $sql = <<<SQL
UPDATE pim_catalog_channel SET conversionUnits = :conversion_units WHERE code = :code
SQL;
        $this->connection->executeQuery($sql, ['conversion_units' => serialize($conversionUnits), 'code' => $channelCode]);
    }
}
