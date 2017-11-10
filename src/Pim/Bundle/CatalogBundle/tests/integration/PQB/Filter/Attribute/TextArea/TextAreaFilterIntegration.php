<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\TextArea;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAreaFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /** @var string Test newlines in TextArea data */
    private $rabbitNewLineData = "Why my rabbit
 is the best?";

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('cat', [
            'values' => [
                'a_text_area' => [['data' => 'cat', 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->createProduct('cattle', [
            'values' => [
                'a_text_area' => [['data' => 'cattle', 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->createProduct('dog', [
            'values' => [
                'a_text_area' => [['data' => 'dog', 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->createProduct('best_dog', [
            'values' => [
                'a_text_area' => [['data' => 'my dog is the most beautiful', 'locale' => null, 'scope' => null]]
            ]
        ]);

        $this->createProduct('best_cat', [
            'values' => [
                'a_text_area' => [
                    [
                        'data' => 'my <bold>cat</bold> is the most <i>beautiful</i><br/>',
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ]
        ]);

        $this->createProduct('best_rabbit', [
            'values' => [
                'a_text_area' => [
                    [
                        'data' => $this->rabbitNewLineData,
                        'locale' => null,
                        'scope' => null,
                    ],
                ],
            ]
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['a_text_area', Operators::STARTS_WITH, 'at']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_text_area', Operators::STARTS_WITH, 'cat']]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->executeFilter([['a_text_area', Operators::STARTS_WITH, 'cats']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_text_area', Operators::STARTS_WITH, 'my dog']]);
        $this->assert($result, ['best_dog']);

        $result = $this->executeFilter([['a_text_area', Operators::STARTS_WITH, 'my cat']]);
        $this->assert($result, ['best_cat']);

        $result = $this->executeFilter([['a_text_area', Operators::STARTS_WITH, 'why my rabbit is']]);
        $this->assert($result, ['best_rabbit']);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_text_area', Operators::CONTAINS, 'at']]);
        $this->assert($result, ['cat', 'cattle', 'best_cat']);

        $result = $this->executeFilter([['a_text_area', Operators::CONTAINS, 'cat']]);
        $this->assert($result, ['cat', 'cattle', 'best_cat']);

        $result = $this->executeFilter([['a_text_area', Operators::CONTAINS, 'most beautiful']]);
        $this->assert($result, ['best_dog', 'best_cat']);

        $result = $this->executeFilter([['a_text_area', Operators::CONTAINS, 'the']]);
        $this->assert($result, ['best_dog', 'best_cat', 'best_rabbit']);

        $result = $this->executeFilter([['a_text_area', Operators::CONTAINS, 'bold>']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_text_area', Operators::CONTAINS, 'cat is the most']]);
        $this->assert($result, ['best_cat']);

        $result = $this->executeFilter([['a_text_area', Operators::CONTAINS, 'my rabbit is']]);
        $this->assert($result, ['best_rabbit']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_text_area', Operators::DOES_NOT_CONTAIN, 'at']]);
        $this->assert($result, ['dog', 'best_dog', 'best_rabbit']);

        $result = $this->executeFilter([['a_text_area', Operators::DOES_NOT_CONTAIN, 'other']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog', 'best_cat', 'best_rabbit']);

        $result = $this->executeFilter([['a_text_area', Operators::DOES_NOT_CONTAIN, '<br/>']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog', 'best_cat', 'best_rabbit']);

        $result = $this->executeFilter([['a_text_area', Operators::DOES_NOT_CONTAIN, 'most beautiful']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_rabbit']);

        $result = $this->executeFilter([['a_text_area', Operators::DOES_NOT_CONTAIN, 'is the']]);
        $this->assert($result, ['cat', 'cattle', 'dog']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_text_area', Operators::EQUALS, 'cats']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_text_area', Operators::EQUALS, 'cat']]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_text_area', Operators::EQUALS, 'my dog is the most beautiful']]);
        $this->assert($result, ['best_dog']);

        $result = $this->executeFilter([
            [
                'a_text_area',
                Operators::EQUALS,
                'my <bold>cat</bold> is the most <i>beautiful</i><br/>',
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_text_area',
                Operators::EQUALS,
                'my cat is the most beautiful',
            ],
        ]);
        $this->assert($result, ['best_cat']);

        $result = $this->executeFilter([
            [
                'a_text_area',
                Operators::EQUALS,
                $this->rabbitNewLineData,
            ],
        ]);
        $this->assert($result, []);

        $result = $this->executeFilter([
            [
                'a_text_area',
                Operators::EQUALS,
                'why my rabbit is the best?',
            ],
        ]);
        $this->assert($result, ['best_rabbit']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_text_area', Operators::IS_EMPTY, null]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_text_area', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog', 'best_cat', 'best_rabbit']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_text_area', Operators::NOT_EQUAL, 'dog']]);
        $this->assert($result, ['cat', 'cattle', 'best_cat', 'best_rabbit', 'best_dog']);

        $result = $this->executeFilter([['a_text_area', Operators::NOT_EQUAL, 'cat']]);
        $this->assert($result, ['cattle', 'dog', 'best_dog', 'best_cat', 'best_rabbit']);

        $result = $this->executeFilter([['a_text_area', Operators::NOT_EQUAL, 'my dog is the most beautiful']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_cat', 'best_rabbit']);

        $result = $this->executeFilter([
            [
                'a_text_area',
                Operators::NOT_EQUAL,
                'my cat is the most beautiful',
            ],
        ]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog', 'best_rabbit']);

        $result = $this->executeFilter([
            [
                'a_text_area',
                Operators::NOT_EQUAL,
                'my <bold>cat</bold> is the most <i>beautiful</i><br/>',
            ],
        ]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_cat', 'best_dog', 'best_rabbit']);

        $result = $this->executeFilter([['a_text_area', Operators::NOT_EQUAL, $this->rabbitNewLineData]]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_cat', 'best_dog', 'best_rabbit']);

        $result = $this->executeFilter([['a_text_area', Operators::NOT_EQUAL, 'why my rabbit is the best?']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_cat', 'best_dog']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_text_area" expects a string as data, "array" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->executeFilter([['a_text_area', Operators::NOT_EQUAL, [[]]]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_text_area" is not supported or does not support operator ">="
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeFilter([['a_text_area', Operators::GREATER_OR_EQUAL_THAN, 'dog']]);
    }
}
