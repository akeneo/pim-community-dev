<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\Sql\Attribute;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetAttributeLabelsInterface;
use Akeneo\Test\Integration\TestCase;
use PHPUnit\Framework\Assert;

class SqlGetAttributeLabelsIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_that_it_returns_labels()
    {
        $result = $this->getAttributeLabels()->forAttributeCodes(['sku', 'nonexistingattribute']);
        $expected = [
            'sku' => [
                'en_US' => 'SKU'
            ]
        ];
        Assert::assertSame($result, $expected);
    }

    private function getAttributeLabels(): GetAttributeLabelsInterface
    {
        return $this->get('akeneo.pim.enrichment.attribute.query.get_labels');
    }
}
