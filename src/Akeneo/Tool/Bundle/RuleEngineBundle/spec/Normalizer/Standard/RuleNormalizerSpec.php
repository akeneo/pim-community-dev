<?php

namespace spec\Akeneo\Tool\Bundle\RuleEngineBundle\Normalizer\Standard;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionTranslation;
use Doctrine\Common\Collections\ArrayCollection;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RuleNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Akeneo\Tool\Bundle\RuleEngineBundle\Normalizer\Standard\RuleNormalizer');
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
        $definition->isEnabled()->willReturn(false);
        $translationEn = new RuleDefinitionTranslation();
        $translationEn->setLocale('en_US');
        $translationEn->setLabel('Tshirt price');
        $translationFr = new RuleDefinitionTranslation();
        $translationFr->setLocale('fr_FR');
        $translationFr->setLabel('Prix Tshirt');
        $definition->getTranslations()->willReturn(new ArrayCollection([
            $translationEn,
            $translationFr
        ]));

        $this->normalize($definition, Argument::cetera())->shouldReturn(
            [
                'code' => 'camera_set_canon_brand',
                'type' => 'foo',
                'priority' => 100,
                'enabled' => false,
                'conditions' => ['my conditions should be here'],
                'actions' => ['my actions should be there'],
                'labels' => [
                    'en_US' => 'Tshirt price',
                    'fr_FR' => 'Prix Tshirt'
                ]
            ]
        );
    }
}
