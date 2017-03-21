<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Media;

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
                'code'                => 'a_localizable_media',
                'type'                => AttributeTypes::IMAGE,
                'localizable'         => true,
                'scopable'            => false
            ]);

            $this->createProduct('product_one', [
                'values' => [
                    'a_localizable_image' => [
                        ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'en_US', 'scope' => null],
                        ['data' => $this->getFixturePath('ziggy.png'), 'locale' => 'fr_FR', 'scope' => null],
                    ]
                ]
            ]);

            $this->createProduct('product_two', [
                'values' => [
                    'a_localizable_image' => [
                        ['data' => $this->getFixturePath('ziggy.png'), 'locale' => 'en_US', 'scope' => null],
                        ['data' => $this->getFixturePath('ziggy.png'), 'locale' => 'fr_FR', 'scope' => null],
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorStartWith()
    {
        $result = $this->execute([['a_localizable_image', Operators::STARTS_WITH, 'aken', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_localizable_image', Operators::STARTS_WITH, 'aken', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorEndWith()
    {
        $result = $this->execute([['a_localizable_image', Operators::ENDS_WITH, 'ziggy.png', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_localizable_image', Operators::ENDS_WITH, 'ziggy.png', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_localizable_image', Operators::ENDS_WITH, 'ziggy', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorContains()
    {
        $result = $this->execute([['a_localizable_image', Operators::CONTAINS, 'ziggy', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two']);

        $result = $this->execute([['a_localizable_image', Operators::CONTAINS, 'ziggy', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->execute([['a_localizable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one']);

        $result = $this->execute([['a_localizable_image', Operators::DOES_NOT_CONTAIN, 'ziggy', ['locale' => 'fr_FR']]]);
        $this->assert($result, []);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['a_localizable_image', Operators::EQUALS, 'ziggy.png', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_localizable_image', Operators::EQUALS, 'ziggy', ['locale' => 'en_US']]]);
        $this->assert($result, []);

        $result = $this->execute([['a_localizable_image', Operators::EQUALS, 'ziggy.png', ['locale' => 'fr_FR']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['a_localizable_image', Operators::IS_EMPTY, [], ['locale' => 'en_US']]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['a_localizable_image', Operators::IS_NOT_EMPTY, [], ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['a_localizable_image', Operators::NOT_EQUAL, 'akeneo.jpg', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_two']);

        $result = $this->execute([['a_localizable_image', Operators::NOT_EQUAL, 'akene', ['locale' => 'en_US']]]);
        $this->assert($result, ['product_one', 'product_two']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_image" expects a locale, none given.
     */
    public function testErrorLocale()
    {
        $this->execute([['a_localizable_image', Operators::NOT_EQUAL, '2016-09-23']]);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyException
     * @expectedExceptionMessage Attribute "a_localizable_image" expects an existing and activated locale, "NOT_FOUND" given.
     */
    public function testLocaleNotFound()
    {
        $this->execute([['a_localizable_image', Operators::NOT_EQUAL, '2016-09-23', ['locale' => 'NOT_FOUND']]]);
    }
}
