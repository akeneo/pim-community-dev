<?php

namespace spec\Pim\Bundle\EnrichBundle\Normalizer;

use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CompletenessCollectionNormalizerSpec extends ObjectBehavior
{
    function let(NormalizerInterface $normalizer) {
        $this->beConstructedWith($normalizer);
    }

    function it_supports_iterables() {
        $this->supportsNormalization(Argument::any())->shouldReturn(false);
    }

    function it_normalize_completnesses_and_index_them($normalizer, AttributeInterface $name, AttributeInterface $description)
    {
        $normalizer->normalize('completeness', 'internal_api', ['a_context_key' => 'context_value'])
            ->willReturn('normalized_completeness');

        $name->getCode()->willReturn('name');
        $description->getCode()->willReturn('description');

        $this->normalize([
            'en_US' => [
                'channels' => [
                    'mobile' => ['missing' => [$name, $description], 'completeness' => 'completeness'],
                    'print'  => ['missing' => [$name, $description], 'completeness' => 'completeness'],
                    'tablet' => ['missing' => [$name, $description], 'completeness' => 'completeness']
                ],
            ],
            'fr_FR' => [
                'channels' => [
                    'mobile' => ['missing' => [$name, $description], 'completeness' => 'completeness'],
                    'print'  => ['missing' => [$name, $description], 'completeness' => 'completeness'],
                    'tablet' => ['missing' => [$name, $description], 'completeness' => 'completeness']
                ]
            ]
        ], 'internal_api', ['a_context_key' => 'context_value'])->shouldReturn([
            'en_US' => [
                'channels' => [
                    'mobile' => ['missing' => ['name', 'description'], 'completeness' => 'normalized_completeness'],
                    'print'  => ['missing' => ['name', 'description'], 'completeness' => 'normalized_completeness'],
                    'tablet' => ['missing' => ['name', 'description'], 'completeness' => 'normalized_completeness']
                ],
            ],
            'fr_FR' => [
                'channels' => [
                    'mobile' => ['missing' => ['name', 'description'], 'completeness' => 'normalized_completeness'],
                    'print'  => ['missing' => ['name', 'description'], 'completeness' => 'normalized_completeness'],
                    'tablet' => ['missing' => ['name', 'description'], 'completeness' => 'normalized_completeness']
                ]
            ]
        ]);
    }
}
