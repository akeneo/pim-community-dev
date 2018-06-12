<?php

namespace spec\Akeneo\EnrichedEntity\back\Infrastructure\Normalizer;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityDetails\EnrichedEntityDetails;
use Akeneo\EnrichedEntity\back\Infrastructure\Normalizer\EnrichedEntityDetailsNormalizer;
use PhpSpec\ObjectBehavior;

class EnrichedEntityDetailsNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntityDetailsNormalizer::class);
    }

    public function it_supports_enriched_entity_on_internal_api_format(EnrichedEntityDetails $designer)
    {
        $this->supportsNormalization($designer, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($designer, 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
    }

    public function it_normalizes_an_enriched_entity()
    {
        $designerDetails = new EnrichedEntityDetails();
        $designerDetails->identifier = 'designer';
        $designerDetails->labels = [
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur',
        ];

        $this->normalize($designerDetails, 'internal_api')->shouldReturn([
            'identifier' => 'designer',
            'labels' => [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur'
            ]
        ]);
    }
}
