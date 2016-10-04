<?php

namespace spec\Akeneo\Bundle\RuleEngineBundle\Normalizer\Standard;

use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RuleNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Normalizer\RuleNormalizer');
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement('Symfony\Component\Serializer\Normalizer\NormalizerInterface');
    }

    function it_supports_standard_normalization(RuleDefinitionInterface $ruleDefinition)
    {
        $this->supportsNormalization($ruleDefinition, 'standard')->shouldBe(true);
        $this->supportsNormalization($ruleDefinition, 'json')->shouldBe(true);
        $this->supportsNormalization($ruleDefinition, 'xml')->shouldBe(true);
    }

    function it_normalizes_rule_definition_to_a_rule(RuleDefinitionInterface $definition)
    {
        $definition->getCode()->willReturn('camera_set_canon_brand');
        $definition->getType()->willReturn('foo');
        $definition->getPriority()->willReturn(100);
        $definition->getContent()->willReturn([
            'conditions' => ['my conditions should be here'],
            'actions' => ['my actions should be there'],
        ]);

        $this->normalize($definition, Argument::cetera())->shouldReturn(
            [
                'code' => 'camera_set_canon_brand',
                'type' => 'foo',
                'priority' => 100,
                'conditions' => ['my conditions should be here'],
                'actions' => ['my actions should be there'],
            ]
        );
    }
}
