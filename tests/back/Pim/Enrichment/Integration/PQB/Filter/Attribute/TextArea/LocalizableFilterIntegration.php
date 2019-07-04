<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\TextArea;

use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
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
            'code'                => 'a_localizable_text_area',
            'type'                => AttributeTypes::TEXTAREA,
            'localizable'         => true,
            'scopable'            => false,
        ]);

        $this->createFamily([
            'code' => 'a_family',
            'attributes' => ['sku', 'a_localizable_text_area']
        ]);

        $this->createProduct('cat', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_text_area' => [
                    ['data' => 'black cat', 'locale' => 'en_US', 'scope' => null],
                    ['data' => 'chat <b>noir</b>', 'locale' => 'fr_FR', 'scope' => null],
                ]
            ]
        ]);

        $this->createProduct('cattle', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_text_area' => [
                    ['data' => 'cattle', 'locale' => 'en_US', 'scope' => null],
                    ['data' => '<h1>cattle</h1>', 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('dog', [
            'family' => 'a_family',
            'values' => [
                'a_localizable_text_area' => [
                    ['data' => 'just a dog...', 'locale' => 'en_US', 'scope' => null],
                    ['data' => 'juste un chien', 'locale' => 'fr_FR', 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', ['family' => 'a_family']);
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['a_localizable_text_area', Operators::STARTS_WITH, 'black', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::STARTS_WITH, 'black', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::STARTS_WITH, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cattle']);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::STARTS_WITH, 'cat', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_localizable_text_area', Operators::CONTAINS, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::CONTAINS, 'nope', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::CONTAINS, 'juste un', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['dog']);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::CONTAINS, 'cattle', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_localizable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['locale' => 'en_US']]]);
        $this->assert($result, ['cattle', 'dog']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_text_area', Operators::EQUALS, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::EQUALS, 'black cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::EQUALS, 'chat noir', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::EQUALS, 'cattle', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_text_area', Operators::IS_EMPTY, null, ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_text_area', Operators::IS_NOT_EMPTY, null, ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_text_area', Operators::NOT_EQUAL, 'dog', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_text_area', Operators::NOT_EQUAL, 'just a dog...', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    public function testErrorLocalizable()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_text_area" expects a locale, none given.');

        $this->executeFilter([['a_localizable_text_area', Operators::NOT_EQUAL, 'data']]);
    }

    public function testLocaleNotFound()
    {
        $this->expectException(InvalidPropertyException::class);
        $this->expectExceptionMessage('Attribute "a_localizable_text_area" expects an existing and activated locale, "NOT_FOUND" given.');

        $this->executeFilter([['a_localizable_text_area', Operators::NOT_EQUAL, 'text', ['locale' => 'NOT_FOUND']]]);
    }
}
