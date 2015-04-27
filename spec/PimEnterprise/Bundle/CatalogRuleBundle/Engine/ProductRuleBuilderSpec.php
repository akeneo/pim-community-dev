<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use Akeneo\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Bundle\RuleEngineBundle\Exception\BuilderException;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class ProductRuleBuilderSpec extends ObjectBehavior
{
    function let(
        DenormalizerInterface $ruleContentDenormalizer,
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $ruleContentDenormalizer,
            $eventDispatcher,
            $validator,
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule'
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleBuilder');
    }

    function it_is_a_rule_builder()
    {
        $this->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Engine\BuilderInterface');
    }

    function it_builds_a_rule($eventDispatcher, $validator, $ruleContentDenormalizer, RuleDefinitionInterface $definition)
    {
        $content = $this->buildRuleContent();

        $definition->getContent()->shouldBeCalled()->willReturn($content);
        $ruleContentDenormalizer->denormalize(Argument::cetera())->shouldBeCalled()->willReturn($content);

        $eventDispatcher->dispatch(RuleEvents::PRE_BUILD, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_BUILD, Argument::any())->shouldBeCalled();
        $validator->validate(Argument::any())->shouldBeCalled()->willReturn([]);

        $this->build($definition)->shouldHaveType('Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface');
    }

    function it_does_not_build_a_rule_with_bad_content(
        $eventDispatcher,
        $ruleContentDenormalizer,
        RuleDefinitionInterface $definition
    ) {
        $definition->getCode()->willReturn('rule1');
        $definition->getContent()->shouldBeCalled()->willReturn([]);
        $ruleContentDenormalizer->denormalize(Argument::cetera())->willThrow(new \LogicException('Bad content!'));

        $eventDispatcher->dispatch(RuleEvents::PRE_BUILD, Argument::any())->shouldBeCalled();

        $this
            ->shouldThrow(new BuilderException('Impossible to build the rule "rule1". Bad content!'))
            ->during('build', [$definition])
        ;
    }

    function it_does_not_build_a_rule_with_a_content_that_is_not_valid(
        $eventDispatcher,
        $validator,
        $ruleContentDenormalizer,
        RuleDefinitionInterface $definition,
        ConstraintViolationListInterface $violations
    ) {
        $content = $this->buildRuleContent();

        $definition->getCode()->willReturn('rule1');
        $definition->getContent()->shouldBeCalled()->willReturn([]);
        $ruleContentDenormalizer->denormalize(Argument::cetera())->shouldBeCalled()->willReturn($content);

        $eventDispatcher->dispatch(RuleEvents::PRE_BUILD, Argument::any())->shouldBeCalled();

        $violations->count()->willReturn(2);
        $violations->rewind()->willReturn(null);
        $violations->valid()->shouldBeCalled();
        $validator->validate(Argument::any())->willReturn($violations);

        $this
            ->shouldThrow('Akeneo\Bundle\RuleEngineBundle\Exception\BuilderException')
            ->during('build', [$definition])
        ;
    }

    /**
     * Do not delete it, this method is used to easily build the rule content that is
     * used in those specs.
     * In case we need to modify the specs, it will be useful.
     *
     * @return string
     */
    private function buildRuleContent()
    {
        $content = [
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
            'actions' => [
                ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['type' => 'copy_value', 'fromField' => 'description', 'toField' => 'description', 'fromLocale' => 'fr_FR', 'toLocale' => 'fr_CH']
            ]
        ];

        return $content;
    }
}
