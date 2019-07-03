<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Filter\Media;

use Akeneo\Pim\Enrichment\Component\Product\Exception\UnsupportedFilterException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('akeneo', [
            'values' => [
                'an_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('akeneo.jpg')), 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('ziggy', [
            'values' => [
                'an_image' => [
                    ['data' => $this->getFileInfoKey($this->getFixturePath('ziggy.png')), 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorStartWith()
    {
        $result = $this->executeFilter([['an_image', Operators::STARTS_WITH, 'aken']]);
        $this->assert($result, ['akeneo']);

        $result = $this->executeFilter([['an_image', Operators::STARTS_WITH, 'keneo']]);
        $this->assert($result, []);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['an_image', Operators::CONTAINS, 'ziggy']]);
        $this->assert($result, ['ziggy']);

        $result = $this->executeFilter([['an_image', Operators::CONTAINS, 'igg']]);
        $this->assert($result, ['ziggy']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['an_image', Operators::DOES_NOT_CONTAIN, 'ziggy']]);
        $this->assert($result, ['akeneo']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['an_image', Operators::EQUALS, 'ziggy.png']]);
        $this->assert($result, ['ziggy']);

        $result = $this->executeFilter([['an_image', Operators::EQUALS, 'ziggy']]);
        $this->assert($result, []);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['an_image', Operators::IS_EMPTY, []]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['an_image', Operators::IS_NOT_EMPTY, []]]);
        $this->assert($result, ['akeneo', 'ziggy']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['an_image', Operators::NOT_EQUAL, 'akeneo.jpg']]);
        $this->assert($result, ['ziggy']);

        $result = $this->executeFilter([['an_image', Operators::NOT_EQUAL, 'akene']]);
        $this->assert($result, ['akeneo', 'ziggy']);
    }

    public function testErrorDataIsMalformed()
    {
        $this->expectException(InvalidPropertyTypeException::class);
        $this->expectExceptionMessage('Property "an_image" expects a string as data, "array" given.');

        $this->executeFilter([['an_image', Operators::CONTAINS, []]]);
    }

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(UnsupportedFilterException::class);
        $this->expectExceptionMessage('Filter on property "an_image" is not supported or does not support operator "BETWEEN"');

        $this->executeFilter([['an_image', Operators::BETWEEN, 'ziggy.png']]);
    }
}
