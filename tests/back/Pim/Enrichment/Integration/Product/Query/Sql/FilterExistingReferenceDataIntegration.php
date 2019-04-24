<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Query\Sql;

use Akeneo\Pim\Enrichment\Bundle\Product\Query\Sql\FilterExistingReferenceData;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class FilterExistingReferenceDataIntegration extends TestCase
{
    function test_it_filters_non_existing_reference_data_codes()
    {
        $referenceDataAttribute = $this->createReferenceDataAttribute();
        $expected = ['aertex'];
        $actual = $this->getQuery()->filter($referenceDataAttribute, ['aertex', 'foo']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    function test_it_returns_nothing_if_no_reference_data_exists()
    {
        $referenceDataAttribute = $this->createReferenceDataAttribute();
        $expected = [];
        $actual = $this->getQuery()->filter($referenceDataAttribute, ['foo', 'bar']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    function test_it_returns_all_existing_reference_data()
    {
        $referenceDataAttribute = $this->createReferenceDataAttribute();
        $expected = ['aertex', 'airdura', 'airguard'];
        $actual = $this->getQuery()->filter($referenceDataAttribute, ['aertex', 'airguard', 'airdura']);

        $this->assertEqualsCanonicalizing($expected, $actual);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createReferenceDataAttribute(): AttributeInterface
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $attribute->setProperties(['reference_data_name' => 'fabrics']);
        $this->get('pim_catalog.updater.attribute')->update($attribute, [
            'code' => 'fabric_attribute',
            'type' => AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT,
            'localizable' => false,
            'scopable' => false,
            'group' => 'other'
        ]);
        $constraints = $this->get('validator')->validate($attribute);
        Assert::count($constraints, 0);
        $this->get('pim_catalog.saver.attribute')->save($attribute);

        return $attribute;
    }

    private function getQuery(): FilterExistingReferenceData
    {
        return $this->get('akeneo.pim.enrichment.product.query.filter_existing_reference_data');
    }
}
