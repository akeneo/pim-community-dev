<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetChannelLabelsInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SqlGetChannelLabelsIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_that_it_returns_labels()
    {
        $result = $this->getChannelLabels()->forChannelCodes(['ecommerce', 'nonexistingchannel']);
        $expected = [
            'ecommerce' => [
                'en_US' => 'Default',
                'de_DE' => 'Standard',
                'fr_FR' => 'Défaut'
            ]
        ];
        Assert::assertSame($result, $expected);
    }

    private function getChannelLabels(): GetChannelLabelsInterface
    {
        return $this->get('akeneo.pim.enrichment.channel.query.get_labels');
    }
}
