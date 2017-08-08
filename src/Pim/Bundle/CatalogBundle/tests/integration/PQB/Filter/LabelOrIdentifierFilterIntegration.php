<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Julien Sanchez <julien@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LabelOrIdentifierFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute(['code' => 'name', 'type' => 'pim_catalog_text']);

        $this->createFamily(['code' => 'tshirt', 'attributes' => ['name'], 'attribute_as_label' => 'name']);

        $this->createProduct('bar', [
            'values' => [
                'name' => [
                    ['data' => 'baz', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->createProduct('baz', ['family' => 'tshirt']);
        $this->createProduct('foo', [
            'family' => 'tshirt',
            'values' => [
                'name' => [
                    ['data' => 'baz', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->createProduct('michel', [
            'values' => [
                'name' => [
                    ['data' => 'didier', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
        $this->createProduct('jean', [
            'family' => 'tshirt',
            'values' => [
                'name' => [
                    ['data' => 'Jean Michel', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);
    }

    public function testSearch()
    {
        $result = $this->executeFilter([['label_or_identifier', Operators::CONTAINS, 'ba']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['label_or_identifier', Operators::CONTAINS, 'bar']]);
        $this->assert($result, ['bar']);

        $result = $this->executeFilter([['label_or_identifier', Operators::CONTAINS, 'baz']]);
        $this->assert($result, ['baz', 'foo']);

        $result = $this->executeFilter([['label_or_identifier', Operators::CONTAINS, 'didier']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['label_or_identifier', Operators::CONTAINS, 'michel']]);
        $this->assert($result, ['michel', 'jean']);
    }
}
