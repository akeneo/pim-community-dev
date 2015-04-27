<?php

namespace spec\Akeneo\Bundle\RuleEngineBundle\Normalizer;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Serializer\SerializerInterface;

class RuleCollectionNormalizerSpec extends ObjectBehavior
{
    function let(SerializerInterface $serializer)
    {
        $serializer->implement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Normalizer\RuleCollectionNormalizer');
    }

    function it_normalizes_rules_definition_to_array(
        RuleDefinitionInterface $definition,
        SerializerInterface $serializer
    ) {
        $definition->getCode()->willReturn('camera_set_canon_brand');
        $definition->getType()->willReturn('foo');
        $definition->getPriority()->willReturn(100);
        $definition->getContent()->willReturn([
            'conditions' => ['my conditions should be here'],
            'actions' => ['my actions should be there'],
        ]);

        $arrayDefinition = [
            'code' => 'camera_set_canon_brand',
            'type' => 'foo',
            'priority' => 100,
            'conditions' => ['my conditions should be here'],
            'actions' => ['my actions should be there'],
        ];

        $serializer->normalize($definition, 'array', [])->willReturn($arrayDefinition);
        $collection = new ArrayCollection([$definition]);
        $this->normalize($collection, 'array', [])->shouldReturn([$arrayDefinition]);
    }

    function it_validates_supports_normalization_in_collection_of_rules()
    {
        $definition = new RuleDefinition();
        $collection = new ArrayCollection([$definition]);
        $this->supportsNormalization($collection, 'array')->shouldBe(true);
        $this->supportsNormalization([$definition], 'array')->shouldBe(true);
        $this->supportsNormalization($collection, 'string')->shouldBe(false);
        $this->supportsNormalization([], 'array')->shouldBe(false);
    }
}
