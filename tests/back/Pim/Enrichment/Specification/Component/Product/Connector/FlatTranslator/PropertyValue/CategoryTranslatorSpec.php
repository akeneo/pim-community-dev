<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Category\GetCategoryTranslations;
use PhpSpec\ObjectBehavior;

class CategoryTranslatorSpec extends ObjectBehavior
{
    function let(
        GetCategoryTranslations $getCategoryTranslations
    ) {
        $this->beConstructedWith($getCategoryTranslations);
    }

    function it_only_supports_category_property()
    {
        $this->supports('categories')->shouldReturn(true);
        $this->supports('other')->shouldReturn(false);
    }

    function it_translates_category_property_values(
        GetCategoryTranslations $getCategoryTranslations
    ) {
        $getCategoryTranslations->byCategoryCodesAndLocale(['men_shoe', 'tshirt_women', 'not_translated_category'], 'fr_FR')
            ->willReturn(['men_shoe' => 'Chaussures pour hommes', 'tshirt_women' => 'Tshirt pour femmes']);

        $this->translate(['men_shoe,tshirt_women,not_translated_category', ''], 'fr_FR', 'ecommerce')
            ->shouldReturn(['Chaussures pour hommes,Tshirt pour femmes,[not_translated_category]', '']);
    }
}
