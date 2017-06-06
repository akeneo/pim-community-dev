<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Sorter\TextArea;

use Pim\Bundle\CatalogBundle\tests\integration\PQB\AbstractProductQueryBuilderTestCase;
use Pim\Component\Catalog\Query\Sorter\Directions;

/**
 * Text area sorter integration tests
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TextAreaSorterIntegration extends AbstractProductQueryBuilderTestCase
{
    /** @var string Test newlines in TextArea data */
    private $superDog = "my dog
 is the best";

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->createProduct('cat', [
            'values' => [
                'a_text_area' => [['data' => 'cat', 'locale' => null, 'scope' => null]],
            ],
        ]);

        $this->createProduct('best_cat', [
            'values' => [
                'a_text_area' => [
                    [
                        'data'   => 'my <bold>cat</bold> is the most <i>beautiful</i><br/>',
                        'locale' => null,
                        'scope'  => null,
                    ],
                ],
            ],
        ]);

        $this->createProduct('super_dog', [
            'values' => [
                'a_text_area' => [
                    [
                        'data'   => $this->superDog,
                        'locale' => null,
                        'scope'  => null,
                    ],
                ],
            ],
        ]);

        $this->createProduct('best_dog', [
            'values' => [
                'a_text_area' => [['data' => 'my dog is the most beautiful', 'locale' => null, 'scope' => null]],
            ],
        ]);

        $this->createProduct('empty_product', []);
    }

    public function testSorterAscending()
    {
        $result = $this->executeSorter([['a_text_area', Directions::ASCENDING]]);
        $this->assertOrder($result, ['cat', 'best_cat', 'super_dog', 'best_dog', 'empty_product']);
    }

    public function testSorterDescending()
    {
        $result = $this->executeSorter([['a_text_area', Directions::DESCENDING]]);
        $this->assertOrder($result, ['best_dog', 'super_dog', 'best_cat', 'cat', 'empty_product']);
    }

    /**
     * @expectedException \Pim\Component\Catalog\Exception\InvalidDirectionException
     * @expectedExceptionMessage Direction "A_BAD_DIRECTION" is not supported
     */
    public function testErrorOperatorNotSupported()
    {
        $this->executeSorter([['a_text_area', 'A_BAD_DIRECTION']]);
    }
}
