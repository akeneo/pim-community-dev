<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Structure\Component\Query\PublicApi\FamilyVariant\GetFamilyVariantTranslations;
use PhpSpec\ObjectBehavior;

class FamilyVariantTranslatorSpec extends ObjectBehavior
{
    function let(
        GetFamilyVariantTranslations $getFamilyVariantTranslations
    ) {
        $this->beConstructedWith($getFamilyVariantTranslations);
    }

    function it_only_supports_family_variant_property()
    {
        $this->supports('family_variant')->shouldReturn(true);
        $this->supports('family')->shouldReturn(false);
    }

    function it_translates_family_variant_property_values(
        GetFamilyVariantTranslations $getFamilyVariantTranslations
    ) {
        $getFamilyVariantTranslations->byFamilyVariantCodesAndLocale(['accessories_color', 'scanners_print', 'unknown'], 'fr_FR')
            ->willReturn(['accessories_color' => 'Accessoires en couleur', 'scanners_print' => 'Scanners impression']);

        $this->translate(['accessories_color', 'scanners_print', 'unknown'], 'fr_FR', 'ecommerce')
            ->shouldReturn(['Accessoires en couleur', 'Scanners impression', '[unknown]']);
    }
}
