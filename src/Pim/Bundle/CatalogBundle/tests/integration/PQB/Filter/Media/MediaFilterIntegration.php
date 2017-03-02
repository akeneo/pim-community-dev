<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Media;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\AbstractFilterTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFilterIntegration extends AbstractFilterTestCase
{
    public function setUp()
    {
        parent::setUp();

        if (1 === self::$count || $this->getConfiguration()->isDatabasePurgedForEachTest()) {
            $this->createProduct('akeneo', [
                'values' => [
                    'an_image' => [
                        ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => null, 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('ziggy', [
                'values' => [
                    'an_image' => [
                        ['data' => $this->getFixturePath('ziggy.png'), 'locale' => null, 'scope' => null]
                    ]
                ]
            ]);

            $this->createProduct('empty_product', []);
        }
    }

    public function testOperatorStartWith()
    {
        $result = $this->execute([['an_image', Operators::STARTS_WITH, 'aken']]);
        $this->assert($result, ['akeneo']);

        $result = $this->execute([['an_image', Operators::STARTS_WITH, 'keneo']]);
        $this->assert($result, []);
    }

    public function testOperatorEndWith()
    {
        $result = $this->execute([['an_image', Operators::ENDS_WITH, 'ziggy.png']]);
        $this->assert($result, ['ziggy']);

        $result = $this->execute([['an_image', Operators::ENDS_WITH, 'akeneo']]);
        $this->assert($result, []);
    }

    public function testOperatorContains()
    {
        $result = $this->execute([['an_image', Operators::CONTAINS, 'ziggy']]);
        $this->assert($result, ['ziggy']);

        $result = $this->execute([['an_image', Operators::CONTAINS, 'igg']]);
        $this->assert($result, ['ziggy']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->execute([['an_image', Operators::DOES_NOT_CONTAIN, 'ziggy']]);
        $this->assert($result, ['akeneo']);
    }

    public function testOperatorEquals()
    {
        $result = $this->execute([['an_image', Operators::EQUALS, 'ziggy.png']]);
        $this->assert($result, ['ziggy']);

        $result = $this->execute([['an_image', Operators::EQUALS, 'ziggy']]);
        $this->assert($result, []);
    }

    public function testOperatorEmpty()
    {
        $result = $this->execute([['an_image', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->execute([['an_image', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['akeneo', 'ziggy']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->execute([['an_image', Operators::NOT_EQUAL, 'akeneo.jpg']]);
        $this->assert($result, ['ziggy']);

        $result = $this->execute([['an_image', Operators::NOT_EQUAL, 'akene']]);
        $this->assert($result, ['akeneo', 'ziggy']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "an_image" expects a string as data, "array" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->execute([['an_image', Operators::CONTAINS, []]]);
    }

    /**
     * @expectedException \LogicException
     * @expectedExceptionMessage Filter on property "an_image" is not supported or does not support operator "BETWEEN"
     */
    public function testErrorOperatorNotSupported()
    {
        $this->execute([['an_image', Operators::BETWEEN, 'ziggy.png']]);
    }
}
