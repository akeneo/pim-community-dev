<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\Text;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Text attribute sorter integration tests
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('cat', [
            'values' => [
                'a_text' => [['data' => 'cat is beautiful', 'locale' => null, 'scope' => null]],
            ],
        ]);

        $this->createProduct('dog', [
            'values' => [
                'a_text' => [['data' => 'dog is wonderful', 'locale' => null, 'scope' => null]],
            ],
        ]);

        // There is no html tags in TEXT attributes usually set in the PIM.
        // This tests shows that if it's the case they are stored as is and not stripped.
        $this->createProduct('best_cat', [
            'values' => [
                'a_text' => [
                    [
                        'data'   => '<bold>dog</bold> is the most <i>beautiful</i><br/>',
                        'locale' => null,
                        'scope'  => null,
                    ],
                ],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_text', Directions::ASCENDING]]);
        $this->assertOrder($result, ['best_cat', 'cat', 'dog', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_text', Directions::DESCENDING]]);
        $this->assertOrder($result, ['dog', 'cat', 'best_cat', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_text', 'A_BAD_DIRECTION']]);
    }
}
