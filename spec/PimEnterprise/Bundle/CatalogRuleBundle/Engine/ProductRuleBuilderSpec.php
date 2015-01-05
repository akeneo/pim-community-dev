<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Engine;

use PhpSpec\ObjectBehavior;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentSerializerInterface;
use PimEnterprise\Bundle\RuleEngineBundle\Event\RuleEvents;
use PimEnterprise\Bundle\RuleEngineBundle\Exception\BuilderException;
use PimEnterprise\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Prophecy\Argument;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class ProductRuleBuilderSpec extends ObjectBehavior
{
    public function let(
        EventDispatcherInterface $eventDispatcher,
        ValidatorInterface $validator,
        ProductRuleContentSerializerInterface $serializer
    ) {
        $this->beConstructedWith(
            $eventDispatcher,
            $validator,
            $serializer,
            'PimEnterprise\Bundle\RuleEngineBundle\Model\Rule'
        );
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Engine\ProductRuleBuilder');
    }

    public function it_is_a_rule_builder()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Engine\BuilderInterface');
    }

    public function it_builds_a_rule($eventDispatcher, $validator, $serializer, RuleDefinitionInterface $definition)
    {
        $strContent = $this->buildRuleContent(true);
        $content = $this->buildRuleContent();

        $definition->getContent()->shouldBeCalled()->willReturn($strContent);
        $serializer->deserialize($strContent)->shouldBeCalled()->willReturn($content);

        $eventDispatcher->dispatch(RuleEvents::PRE_BUILD, Argument::any())->shouldBeCalled();
        $eventDispatcher->dispatch(RuleEvents::POST_BUILD, Argument::any())->shouldBeCalled();
        $validator->validate(Argument::any())->shouldBeCalled()->willReturn([]);

        $this->build($definition)->shouldHaveType('PimEnterprise\Bundle\RuleEngineBundle\Model\RuleInterface');
    }

    public function it_does_not_build_a_rule_with_bad_content(
        $eventDispatcher,
        $serializer,
        RuleDefinitionInterface $definition
    ) {
        $strContent = $this->buildRuleContent(true);

        $definition->getCode()->willReturn('rule1');
        $definition->getContent()->shouldBeCalled()->willReturn($strContent);
        $serializer->deserialize($strContent)->willThrow(new \LogicException('Bad content!'));

        $eventDispatcher->dispatch(RuleEvents::PRE_BUILD, Argument::any())->shouldBeCalled();

        $this
            ->shouldThrow(new BuilderException('Impossible to build the rule "rule1". Bad content!'))
            ->during('build', [$definition])
        ;
    }

    public function it_does_not_build_a_rule_with_that_is_not_valid(
        $eventDispatcher,
        $validator,
        $serializer,
        RuleDefinitionInterface $definition
    ) {
        $strContent = $this->buildRuleContent(true);
        $content = $this->buildRuleContent();

        $definition->getCode()->willReturn('rule1');
        $definition->getContent()->shouldBeCalled()->willReturn($strContent);
        $serializer->deserialize($strContent)->shouldBeCalled()->willReturn($content);

        $eventDispatcher->dispatch(RuleEvents::PRE_BUILD, Argument::any())->shouldBeCalled();

        $validator->validate(Argument::any())->willReturn(['errors']);

        $this
            ->shouldThrow(
                new BuilderException('Impossible to build the rule "rule1" as it does not appear to be valid.')
            )
            ->during('build', [$definition])
        ;
    }

    /**
     * Do not delete it, this method is used to easily build the rule content that is
     * used in those specs.
     * In case we need to modify the specs, it will be useful.
     *
     * @param bool $encode
     *
     * @return string
     */
    private function buildRuleContent($encode = false)
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

        if (true === $encode) {
            $content = json_encode($content);
        }

        return $content;
    }
}
