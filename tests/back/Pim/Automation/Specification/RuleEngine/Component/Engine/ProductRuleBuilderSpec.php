<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Engine;

use Akeneo\Pim\Automation\RuleEngine\Component\Engine\ProductRuleBuilder;
use Akeneo\Tool\Bundle\RuleEngineBundle\Engine\BuilderInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Event\RuleEvents;
use Akeneo\Tool\Bundle\RuleEngineBundle\Exception\BuilderException;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ProductRuleBuilderSpec extends ObjectBehavior
{
    function let(
        DenormalizerInterface $ruleContentDenormalizer,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->beConstructedWith(
            $ruleContentDenormalizer,
            $eventDispatcher,
            Rule::class
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductRuleBuilder::class);
    }

    function it_is_a_rule_builder()
    {
        $this->shouldHaveType(BuilderInterface::class);
    }

    function it_builds_a_rule($eventDispatcher, $ruleContentDenormalizer, RuleDefinitionInterface $definition)
    {
        $content = $this->buildRuleContent();

        $definition->getContent()->shouldBeCalled()->willReturn($content);
        $ruleContentDenormalizer->denormalize(Argument::cetera())->shouldBeCalled()->willReturn($content);

        $eventDispatcher->dispatch(Argument::any(), RuleEvents::PRE_BUILD)->shouldBeCalled();
        $eventDispatcher->dispatch(Argument::any(), RuleEvents::POST_BUILD)->shouldBeCalled();

        $this->build($definition)->shouldHaveType(RuleInterface::class);
    }

    function it_does_not_build_a_rule_with_bad_content(
        $eventDispatcher,
        $ruleContentDenormalizer,
        RuleDefinitionInterface $definition
    ) {
        $definition->getCode()->willReturn('rule1');
        $definition->getContent()->shouldBeCalled()->willReturn([]);
        $ruleContentDenormalizer->denormalize(Argument::cetera())->willThrow(new \LogicException('Bad content!'));

        $eventDispatcher->dispatch(Argument::any(), RuleEvents::PRE_BUILD)->shouldBeCalled();

        $this
            ->shouldThrow(new BuilderException('Impossible to build the rule "rule1". Bad content!'))
            ->during('build', [$definition])
        ;
    }

    /**
     * Do not delete it, this method is used to easily build the rule content that is
     * used in those specs.
     * In case we need to modify the specs, it will be useful.
     *
     * @return array
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
