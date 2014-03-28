<?php

namespace spec\Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @require \MongoId
 */
class FieldsTransformerSpec extends ObjectBehavior
{
    function it_transforms_product_fields_result(\MongoId $id)
    {
        $locale = 'fr_FR';
        $id->__toString()->willReturn(42);
        $result = [
            '_id'     => $id,
            'created' => null,
            'updated' => null,
            'enabled' => null,
        ];

        $expected = [
            'created' => null,
            'updated' => null,
            'enabled' => false,
            'id'      => 42,
            'dataLocale' => 'fr_FR'
        ];

        $this->transform($result, $locale)->shouldReturn($expected);
    }
}
