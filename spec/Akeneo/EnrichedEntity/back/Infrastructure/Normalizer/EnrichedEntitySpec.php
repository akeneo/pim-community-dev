<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\back\Infrastructure\Normalizer;

use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntity as EnrichedEntityModel;
use Akeneo\EnrichedEntity\back\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\back\Domain\Model\LabelCollection;
use Akeneo\EnrichedEntity\back\Infrastructure\Normalizer\EnrichedEntity;
use PhpSpec\ObjectBehavior;

class EnrichedEntitySpec extends ObjectBehavior
{
    public function it_is_initializable()
    {
        $this->shouldHaveType(EnrichedEntity::class);
    }

    public function it_supports_enriched_entity_on_internal_api_format(EnrichedEntityModel $designer)
    {
        $this->supportsNormalization($designer, 'internal_api')->shouldReturn(true);
        $this->supportsNormalization($designer, 'standard')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'internal_api')->shouldReturn(false);
    }

    public function it_normalizes_an_enriched_entity()
    {
        $designerIdentifier = EnrichedEntityIdentifier::fromString('designer');
        $designerLabels = [
            'en_US' => 'Designer',
            'fr_FR' => 'Concepteur'
        ];
        $designer = EnrichedEntityModel::create($designerIdentifier, $designerLabels);

        $this->normalize($designer, 'internal_api')->shouldReturn([
            'identifier' => 'designer',
            'labels' => [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur'
            ]
        ]);
    }
}
