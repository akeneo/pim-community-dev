<?php

namespace AkeneoTest\Pim\Enrichment\Integration\PQB\Sorter\Text;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidDirectionException;
use Akeneo\Pim\Enrichment\Component\Product\Query\Sorter\Directions;
use AkeneoTest\Pim\Enrichment\Integration\PQB\AbstractProductQueryBuilderTestCase;

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
    protected function setUp(): void
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

    public function testErrorOperatorNotSupported()
    {
        $this->expectException(InvalidDirectionException::class);
        $this->expectExceptionMessage('Direction "A_BAD_DIRECTION" is not supported');

        $this->executeSorter([['a_text', 'A_BAD_DIRECTION']]);
    }
}
