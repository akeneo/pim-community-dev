<?php

namespace Akeneo\Test\Channel\Integration\Query\Sql;

use Akeneo\Channel\API\Query\Channel;
use Akeneo\Channel\API\Query\FindChannels;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

final class SqlFindChannelsIntegration extends TestCase
{
    private FindChannels $sqlFindChannels;

    public function setUp(): void
    {
        parent::setUp();

        $this->sqlFindChannels = $this->get(
            'Akeneo\Channel\Infrastructure\Query\Sql\SqlFindChannels'
        );
    }

    public function test_it_finds_all_channels(): void
    {
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
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
