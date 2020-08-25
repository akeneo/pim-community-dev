<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelLabelsInterface;
use PhpSpec\ObjectBehavior;

class ParentTranslatorSpec extends ObjectBehavior
{
    function let(
        GetProductModelLabelsInterface $getProductModelLabels
    ) {
        $this->beConstructedWith($getProductModelLabels);
    }

    function it_only_supports_parent_property()
    {
        $this->supports('parent')->shouldReturn(true);
        $this->supports('categories')->shouldReturn(false);
        $this->supports('something_else')->shouldReturn(false);
    }

    function it_translates_parent_property_values(
        GetProductModelLabelsInterface $getProductModelLabels
    ) {
        $getProductModelLabels->byCodesAndLocaleAndScope(['braided-hat', 'bag', 'unknown'], 'fr_FR', 'ecommerce')
            ->willReturn(['braided-hat' => 'Chapeau tressé', 'bag' => 'Sac']);

        $this->translate(['braided-hat', 'bag', 'unknown'], 'fr_FR', 'ecommerce')->shouldReturn(['Chapeau tressé', 'Sac', '[unknown]']);
    }
}
