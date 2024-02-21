<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Number;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetFamily;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
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
            'code'                => 'a_localizable_number',
            'type'                => AttributeTypes::NUMBER,
            'localizable'         => true,
            'scopable'            => false,
            'negative_allowed'    => true,
            'decimals_allowed'    => true,
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_number']
        ]);

        $this->createProduct('product_one', [
            new SetFamily('a_family'),
            new SetNumberValue('a_localizable_number', null, 'en_US', -15),
            new SetNumberValue('a_localizable_number', null, 'fr_FR', -14),
        ]);

        $this->createProduct('product_two', [
            new SetFamily('a_family'),
            new SetNumberValue('a_localizable_number', null, 'en_US', 19),
        ]);

        $this->createProduct('empty_product', [new SetFamily('a_family')]);
    }

    public function testOperatorInferior()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::LOWER_THAN, -14, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_number', Operators::LOWER_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorInferiorOrEqual()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::LOWER_OR_EQUAL_THAN, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_number', Operators::LOWER_OR_EQUAL_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::EQUALS, 15, ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_number', Operators::EQUALS, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperior()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_THAN, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_THAN, -14.0001, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorSuperiorOrEqual()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_OR_EQUAL_THAN, -15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_number', Operators::GREATER_OR_EQUAL_THAN, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::IS_EMPTY, 0, ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);

        $result = $this->executeFilter([['a_localizable_number', Operators::IS_EMPTY, 0, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_two', 'empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::IS_NOT_EMPTY, 0, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, 15, ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, -15, ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one']);

        $result = $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, -14, ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testErrorLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_number" expects a locale, none given.');

        $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, 12]]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_number" expects an existing and activated locale, "NOT_FOUND" given.');

        $this->executeFilter([['a_localizable_number', Operators::NOT_EQUAL, 12, ['locale' => 'NOT_FOUND']]]);
    }
}
