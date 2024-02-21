<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Group\GetGroupTranslations;
use PhpSpec\ObjectBehavior;

class GroupTranslatorSpec extends ObjectBehavior
{
    function let(
        GetGroupTranslations $getGroupTranslations
    ) {
        $this->beConstructedWith($getGroupTranslations);
    }

    function it_only_supports_group_property()
    {
        $this->supports('groups')->shouldReturn(true);
        $this->supports('other')->shouldReturn(false);
    }

    function it_translates_group_property_values(GetGroupTranslations $getGroupTranslations)
    {
        $getGroupTranslations->byGroupCodesAndLocale(['tshirt', 'jeans', 'not_translated_group'], 'fr_FR')
            ->willReturn(['tshirt' => 'Tshirt', 'jeans' => 'Jeans']);

        $this->translate(['tshirt,jeans,not_translated_group', ''], 'fr_FR', 'ecommerce')->shouldReturn(['Tshirt,Jeans,[not_translated_group]', '']);
    }
}
