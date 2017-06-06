<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter\Text;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Filter\Operators;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextFilterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('cat', [
            'values' => [
                'a_text' => [['data' => 'cat', 'locale' => null, 'scope' => null]],
            ],
        ]);

        $this->createProduct('cattle', [
            'values' => [
                'a_text' => [['data' => 'cattle', 'locale' => null, 'scope' => null]],
            ],
        ]);

        $this->createProduct('dog', [
            'values' => [
                'a_text' => [['data' => 'dog', 'locale' => null, 'scope' => null]],
            ],
        ]);

        $this->createProduct('best_dog', [
            'values' => [
                'a_text' => [['data' => 'my dog is the most beautiful', 'locale' => null, 'scope' => null]],
            ],
        ]);

        // There is no html tags in TEXT attributes usually set in the PIM.
        // This tests shows that if it's the case they are stored as is and not stripped.
        $this->createProduct('best_cat', [
            'values' => [
                'a_text' => [
                    [
                        'data'   => 'my <bold>cat</bold> is the most <i>beautiful</i><br/>',
                        'locale' => null,
                        'scope'  => null,
                    ],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testOperatorStartsWith()
    {
        $result = $this->executeFilter([['a_text', Operators::STARTS_WITH, 'at']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_text', Operators::STARTS_WITH, 'cat']]);
        $this->assert($result, ['cat', 'cattle']);

        $result = $this->executeFilter([['a_text', Operators::STARTS_WITH, 'cats']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_text', Operators::STARTS_WITH, 'my dog']]);
        $this->assert($result, ['best_dog']);

        $result = $this->executeFilter([['a_text', Operators::STARTS_WITH, 'my cat']]);
        $this->assert($result, []);
    }

    public function testOperatorContains()
    {
        $result = $this->executeFilter([['a_text', Operators::CONTAINS, 'at']]);
        $this->assert($result, ['cat', 'cattle', 'best_cat']);

        $result = $this->executeFilter([['a_text', Operators::CONTAINS, 'cat']]);
        $this->assert($result, ['cat', 'cattle', 'best_cat']);

        $result = $this->executeFilter([['a_text', Operators::CONTAINS, 'most beautiful']]);
        $this->assert($result, ['best_dog']);

        $result = $this->executeFilter([['a_text', Operators::CONTAINS, 'the']]);
        $this->assert($result, ['best_dog', 'best_cat']);

        $result = $this->executeFilter([['a_text', Operators::CONTAINS, 'bold>']]);
        $this->assert($result, ['best_cat']);
    }

    public function testOperatorDoesNotContain()
    {
        $result = $this->executeFilter([['a_text', Operators::DOES_NOT_CONTAIN, 'at']]);
        $this->assert($result, ['dog', 'best_dog']);

        $result = $this->executeFilter([['a_text', Operators::DOES_NOT_CONTAIN, 'other']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog', 'best_cat']);

        $result = $this->executeFilter([['a_text', Operators::DOES_NOT_CONTAIN, '<br/>']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog']);

        $result = $this->executeFilter([['a_text', Operators::DOES_NOT_CONTAIN, 'most beautiful']]);

        // best_cat does not contain "most beautiful" because it is wrapped in HTML tags
        $this->assert($result, ['cat', 'best_cat', 'cattle', 'dog']);
    }

    public function testOperatorEquals()
    {
        $result = $this->executeFilter([['a_text', Operators::EQUALS, 'cats']]);
        $this->assert($result, []);

        $result = $this->executeFilter([['a_text', Operators::EQUALS, 'cat']]);
        $this->assert($result, ['cat']);

        $result = $this->executeFilter([['a_text', Operators::EQUALS, 'my dog is the most beautiful']]);
        $this->assert($result, ['best_dog']);

        $result = $this->executeFilter([
            [
                'a_text',
                Operators::EQUALS,
                'my <bold>cat</bold> is the most <i>beautiful</i><br/>',
            ],
        ]);
        $this->assert($result, ['best_cat']);
    }

    public function testOperatorEmpty()
    {
        $result = $this->executeFilter([['a_text', Operators::IS_EMPTY, null]]);
        $this->assert($result, ['empty_product']);
    }

    public function testOperatorNotEmpty()
    {
        $result = $this->executeFilter([['a_text', Operators::IS_NOT_EMPTY, null]]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog', 'best_cat']);
    }

    public function testOperatorDifferent()
    {
        $result = $this->executeFilter([['a_text', Operators::NOT_EQUAL, 'dog']]);
        $this->assert($result, ['cat', 'cattle', 'best_cat', 'best_dog']);

        $result = $this->executeFilter([['a_text', Operators::NOT_EQUAL, 'cat']]);
        $this->assert($result, ['cattle', 'dog', 'best_dog', 'best_cat']);

        $result = $this->executeFilter([['a_text', Operators::NOT_EQUAL, 'my dog is the most beautiful']]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_cat']);

        $result = $this->executeFilter([
            [
                'a_text',
                Operators::NOT_EQUAL,
                'my <bold>cat</bold> is the most <i>beautiful</i><br/>',
            ],
        ]);
        $this->assert($result, ['cat', 'cattle', 'dog', 'best_dog']);
    }

    /**
     * @expectedException \Akeneo\Component\StorageUtils\Exception\InvalidPropertyTypeException
     * @expectedExceptionMessage Property "a_text" expects a string as data, "array" given.
     */
    public function testErrorDataIsMalformed()
    {
        $this->executeFilter([['a_text', Operators::NOT_EQUAL, [[]]]]);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\UnsupportedFilterException
     * @expectedExceptionMessage Filter on property "a_text" is not supported or does not support operator ">="
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeFilter([['a_text', Operators::GREATER_OR_EQUAL_THAN, 'dog']]);
    }
}
