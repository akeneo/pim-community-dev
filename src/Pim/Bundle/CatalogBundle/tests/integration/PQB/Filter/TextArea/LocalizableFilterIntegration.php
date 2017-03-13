<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\TextArea;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableFilterIntegration extends AbstractFilterTestCase
{
    /**
     * @{@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createAttribute([
                'code'                => 'a_localizable_text_area',
                'type'                => AttributeTypes::TEXTAREA,
                'localizable'         => true,
                'scopable'            => false,
            ]);

            $this->createProduct('cat', [
                'values' => [
                    'a_localizable_text_area' => [
                        ['data' => 'black cat', 'locale' => 'en_US', 'scope' => null],
                        ['data' => 'chat <b>noir</b>', 'locale' => 'fr_FR', 'scope' => null],
                    ]
                ]
            ]);

            $this->createProduct('cattle', [
                'values' => [
                    'a_localizable_text_area' => [
                        ['data' => 'cattle', 'locale' => 'en_US', 'scope' => null],
                        ['data' => '<h1>cattle</h1>', 'locale' => 'fr_FR', 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('dog', [
                'values' => [
                    'a_localizable_text_area' => [
                        ['data' => 'just a dog...', 'locale' => 'en_US', 'scope' => null],
                        ['data' => 'juste un chien', 'locale' => 'fr_FR', 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorStartsWith()
    {
        $result = $this->execute([['a_localizable_text_area', Operators::STARTS_WITH, 'black', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_text_area', Operators::STARTS_WITH, 'black', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->execute([['a_localizable_text_area', Operators::STARTS_WITH, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cattle']);

        $result = $this->execute([['a_localizable_text_area', Operators::STARTS_WITH, 'cat', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorContains()
    {
        $result = $this->execute([['a_localizable_text_area', Operators::CONTAINS, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_localizable_text_area', Operators::CONTAINS, 'nope', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_text_area', Operators::CONTAINS, 'just un', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['dog']);

        $result = $this->execute([['a_localizable_text_area', Operators::CONTAINS, 'cattle', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->execute([['a_localizable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'empty_product']);

        $result = $this->execute([['a_localizable_text_area', Operators::DOES_NOT_CONTAIN, 'black', ['locale' => 'en_US']]]);
        $this->assert($result, ['cattle', 'dog', 'empty_product']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_localizable_text_area', Operators::EQUALS, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_text_area', Operators::EQUALS, 'black cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->execute([['a_localizable_text_area', Operators::EQUALS, 'chat noir', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cat']);

        $result = $this->execute([['a_localizable_text_area', Operators::EQUALS, 'cattle', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_localizable_text_area', Operators::IS_EMPTY, null, ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_localizable_text_area', Operators::IS_NOT_EMPTY, null, ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_localizable_text_area', Operators::NOT_EQUAL, 'dog', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->execute([['a_localizable_text_area', Operators::NOT_EQUAL, 'just a dog...', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_text_area" expects a locale, none given.
     */
    public function testErrorLocalizable()
    {
        $this->execute([['a_localizable_text_area', Operators::NOT_EQUAL, 'data']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_text_area" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->execute([['a_localizable_text_area', Operators::NOT_EQUAL, 'text', ['locale' => 'NOT_FOUND']]]);
    }
}
