<?php

namespace spec\Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FlatToStandard\ProductModel;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ProductModelSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModel::class);
    }

    function it_is_an_array_converter()
    {
        $this->shouldImplement(ArrayConverterInterface::class);
    }

    function it_convert_flat_product_model_to_standard_format()
    {
        // Mapping (categories, family variant) (pas group et family)

        // Exclude association field (pas group)

        // Validation du tableau de données

        $this->convert([

        ])->shouldReturn([

        ]);
    }
}
