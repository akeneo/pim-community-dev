<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\TextArea;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableScopableFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createAttribute([
            'code'                => 'a_localizable_scopable_text_area',
            'type'                => AttributeTypes::TEXTAREA,
            'localizable'         => true,
            'scopable'            => true,
        ]);

        $this->createProduct('cat', [
            'values' => [
                'a_localizable_scopable_text_area' => [
                    ['data' => 'black cat', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'cat', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => 'chat noir', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'chat', 'locale' => 'fr_FR', 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('cattle', [
            'values' => [
                'a_localizable_scopable_text_area' => [
                    ['data' => 'cattle', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'cattle', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => 'bétail', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'bétail', 'locale' => 'fr_FR', 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('dog', [
            'values' => [
                'a_localizable_scopable_text_area' => [
                    ['data' => 'just a dog...', 'locale' => 'en_US', 'scope' => 'ecommerce'],
                    ['data' => 'dog', 'locale' => 'en_US', 'scope' => 'tablet'],
                    ['data' => 'juste un chien...', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
                    ['data' => 'chien', 'locale' => 'fr_FR', 'scope' => 'tablet']
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::STARTS_WITH, 'black', ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::STARTS_WITH, 'black', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::STARTS_WITH, 'cat', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::CONTAINS, 'cat', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::CONTAINS, 'nope', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, []);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cattle', 'dog']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::EQUALS, 'cat', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::EQUALS, 'cat', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::EQUALS, 'cat', ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_EMPTY, null, ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['scope' => 'ecommerce', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['scope' => 'tablet', 'locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::IS_NOT_EMPTY, null, ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::NOT_EQUAL, 'dog', ['scope' => 'ecommerce', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->executeFilter([['a_localizable_scopable_text_area', Operators::NOT_EQUAL, 'dog', ['scope' => 'tablet', 'locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_scopable_text_area" expects a locale, none given.
     */
    public function testErrorLocalizable()
    {
        $this->executeFilter([['a_localizable_scopable_text_area', Operators::NOT_EQUAL, 'data']]);
    }
}
