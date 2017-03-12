<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\String;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
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
                'code'                => 'a_localizable_text',
                'type'                => AttributeTypes::TEXT,
                'localizable'         => true,
                'scopable'            => false,
            ]);

            $this->createProduct('cat', [
                'values' => [
                    'a_localizable_text' => [
                        ['data' => 'black cat', 'locale' => 'en_US', 'scope' => null],
                        ['data' => 'chat noir', 'locale' => 'fr_FR', 'scope' => null],
                    ]
                ]
            ]);

            $this->createProduct('cattle', [
                'values' => [
                    'a_localizable_text' => [
                        ['data' => 'cattle', 'locale' => 'en_US', 'scope' => null],
                        ['data' => 'cattle', 'locale' => 'fr_FR', 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('dog', [
                'values' => [
                    'a_localizable_text' => [
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
        $result = $this->execute([['a_localizable_text', Operators::STARTS_WITH, 'black', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_text', Operators::STARTS_WITH, 'black', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat']);

        $result = $this->execute([['a_localizable_text', Operators::STARTS_WITH, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cattle']);
    }

    public function testOperatorEndsWith()
    {
        $result = $this->execute([['a_localizable_text', Operators::ENDS_WITH, 'ca', ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_text', Operators::ENDS_WITH, 'cat', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_text', Operators::ENDS_WITH, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat']);
    }

    public function testOperatorContains()
    {
        $result = $this->execute([['a_localizable_text', Operators::CONTAINS, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->execute([['a_localizable_text', Operators::CONTAINS, 'nope', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->execute([['a_localizable_text', Operators::DOES_NOT_CONTAIN, 'black', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'empty_product']);

        $result = $this->execute([['a_localizable_text', Operators::DOES_NOT_CONTAIN, 'black', ['locale' => 'en_US']]]);
        $this->assert($result, ['cattle', 'dog', 'empty_product']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_localizable_text', Operators::EQUALS, 'cat', ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_text', Operators::EQUALS, 'black cat', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_localizable_text', Operators::IS_EMPTY, null, ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_localizable_text', Operators::IS_NOT_EMPTY, null, ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_localizable_text', Operators::NOT_EQUAL, 'dog', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle', 'dog']);

        $result = $this->execute([['a_localizable_text', Operators::NOT_EQUAL, 'just a dog...', ['locale' => 'en_US']]]);
        $this->assert($result, ['cat', 'cattle']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_text" expects a locale, none given.
     */
    public function testErrorLocalizable()
    {
        $this->execute([['a_localizable_text', Operators::NOT_EQUAL, 'data']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_text" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->execute([['a_localizable_text', Operators::NOT_EQUAL, 'text', ['locale' => 'NOT_FOUND']]]);
    }
}
