<?php

namespace spec\Akeneo\EnrichedEntity\back\Infrastructure\Normalizer;

use Akeneo\EnrichedEntity\back\Application\EnrichedEntity\EnrichedEntityList\EnrichedEntityItem;
use Akeneo\EnrichedEntity\back\Infrastructure\Normalizer\EnrichedEntityItemNormalizer;
use PhpSpec\ObjectBehavior;

class EnrichedEntityItemNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntityItemNormalizer::class);
    }

    public function it_supports_enriched_entity_on_internal_api_format(EnrichedEntityItem $designer)
    {
        $this->supportsNormalization($designer, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($designer, 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
    }

    public function it_normalizes_an_enriched_entity()
    {
        $designerItem = new EnrichedEntityItem();
        $designerItem->identifier = 'designer';
        $designerItem->labels = [
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur',
        ];

        $this->normalize($designerItem, 'internal_api')->shouldReturn([
            'identifier' => 'designer',
            'labels' => [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur'
            ]
        ]);
    }
}
