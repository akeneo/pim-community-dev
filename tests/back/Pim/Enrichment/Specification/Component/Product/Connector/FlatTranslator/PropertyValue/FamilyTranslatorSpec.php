<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\PropertyValue;

use Akeneo\Pim\Structure\Component\Query\PublicApi\Family\GetFamilyTranslations;
use PhpSpec\ObjectBehavior;

class FamilyTranslatorSpec extends ObjectBehavior
{
    function let(
        GetFamilyTranslations $getFamilyTranslations
    ) {
        $this->beConstructedWith($getFamilyTranslations);
    }

    function it_only_supports_family_property()
    {
        $this->supports('family')->shouldReturn(true);
        $this->supports('other')->shouldReturn(false);
    }

    function it_translates_family_property_values(
        GetFamilyTranslations $getFamilyTranslations
    ) {
        $getFamilyTranslations->byFamilyCodesAndLocale(['accessories', 'scanners', 'unknown'], 'fr_FR')
            ->willReturn(['accessories' => 'Accessoires', 'scanners' => 'Scanners']);

        $this->translate(['accessories', 'scanners', 'unknown'], 'fr_FR', 'ecommerce')
            ->shouldReturn(['Accessoires', 'Scanners', '[unknown]']);
    }
}
