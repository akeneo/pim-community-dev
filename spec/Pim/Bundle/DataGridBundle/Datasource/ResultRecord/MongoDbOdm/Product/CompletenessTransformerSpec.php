<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CompletenessTransformerSpec extends ObjectBehavior
{
    /**
     * @require \MongoId
     */
    function it_transforms_product_completeness_result(\MongoId $id)
    {
        $locale = 'fr_FR';
        $scope  = 'ecommerce';
        $result = [
            'normalizedData' => [
                'completenesses' => [
                    'ecommerce-en_US' => 25,
                    'ecommerce-fr_FR' => 50,
                    'mobile-en_US' => 75,
                    'mobile-fr_FR' => 100
                ]
            ],
        ];

        $expected = $result + ['ratio' => '50'];

        $this->transform($result, $locale, $scope)->shouldReturn($expected);
    }
}
