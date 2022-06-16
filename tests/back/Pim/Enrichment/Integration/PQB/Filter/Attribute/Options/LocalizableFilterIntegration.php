<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Options;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_multi_select',
            'type'                => AttributeTypes::OPTION_MULTI_SELECT,
            'localizable'         => true,
            'scopable'            => false
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_localizable_multi_select',
            'code'      => 'orange'
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_localizable_multi_select',
            'code'      => 'black'
        ]);

        $this->createAttributeOption([
            'attribute' => 'a_localizable_multi_select',
            'code'      => 'purple'
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_multi_select']
        ]);

        $this->createProduct('product_one', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_localizable_multi_select', null, 'en_US', ['orange']),
            new SetMultiSelectValue('a_localizable_multi_select', null, 'fr_FR', ['black', 'purple']),
        ]);

        $this->createProduct('product_two', [
            new SetFamily('a_family'),
            new SetMultiSelectValue('a_localizable_multi_select', null, 'en_US', ['black', 'orange']),
            new SetMultiSelectValue('a_localizable_multi_select', null, 'fr_FR', ['black', 'orange']),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
    }

    public function testOperatorIn()
    {
        $result = $this->executeFilter([['a_localizable_multi_select', Operators::IN_LIST, ['purple'], ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_multi_select', Operators::IN_LIST, ['black'], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_multi_select', Operators::IN_LIST, ['orange', 'black'], ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_multi_select', Operators::IS_EMPTY, [], ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_multi_select', Operators::IS_NOT_EMPTY, [], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorNotIn()
    {
        $result = $this->executeFilter([['a_localizable_multi_select', Operators::NOT_IN_LIST, ['black'], ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product', 'product_one']);
    }

    public function testErrorOptionLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_multi_select" expects a locale, none given.');

        $this->executeFilter([['a_localizable_multi_select', Operators::IN_LIST, ['orange']]]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_multi_select" expects an existing and activated locale, "NOT_FOUND" given.');

        $this->executeFilter([['a_localizable_multi_select', Operators::IN_LIST, ['orange'], ['locale' => 'NOT_FOUND']]]);
    }
}
