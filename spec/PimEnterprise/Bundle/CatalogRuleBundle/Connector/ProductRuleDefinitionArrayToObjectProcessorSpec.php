<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Connector;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use PimEnterprise\Bundle\CatalogRuleBundle\Serializer\ProductRuleContentSerializerInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

class ProductRuleDefinitionArrayToObjectProcessorSpec extends ObjectBehavior
{
    function let(
        ReferableEntityRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator
    ) {
        $this->beConstructedWith(
            $repository,
            $denormalizer,
            $validator,
            'Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition',
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule'
        );

        $repository->getReferenceProperties()->willReturn(['code']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('PimEnterprise\Bundle\CatalogRuleBundle\Connector\ProductRuleDefinitionArrayToObjectProcessor');
    }

    function it_is_an_import_processor()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Processor\ArrayToObject\AbstractProcessor');
    }

    function it_processes_a_new_valid_item(
        $repository,
        $denormalizer,
        $validator,
        RuleInterface $rule,
        ConstraintViolationListInterface $violations
    ) {
        $item = $this->getRuleArray();

        $repository->findByReference(Argument::any())->shouldBeCalled()->willReturn(null);
        $denormalizer->denormalize(
            $item,
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule',
            null,
            ['definitionObject' => null]
        )->shouldBeCalled()->willReturn($rule);
        $validator->validate($rule)->shouldBeCalled()->willReturn($violations);

        $rule->getCode()->willReturn('discharge_fr_description');
        $rule->getPriority()->willReturn(100);
        $rule->getType()->willReturn('product');
        $rule->getContent()->willReturn($this->getRuleContentArray());

        $definition = new RuleDefinition();
        $definition->setCode('discharge_fr_description');
        $definition->setPriority(100);
        $definition->setType('product');
        $definition->setContent($this->getRuleContentArray());

        $this->process($item)->shouldBeValidRuleDefinition($definition);
    }

    function it_processes_an_existing_valid_item(
        $repository,
        $denormalizer,
        $validator,
        RuleInterface $rule,
        ConstraintViolationListInterface $violations
    ) {
        $item = $this->getRuleArray();

        $rule->getCode()->willReturn('discharge_fr_description');
        $rule->getPriority()->willReturn(100);
        $rule->getType()->willReturn('product');
        $rule->getContent()->willReturn($this->getRuleContentArray());

        $definition = new RuleDefinition();
        $definition->setCode('discharge_fr_description');
        $definition->setPriority(100);
        $definition->setType('product');
        $definition->setContent($this->getRuleContentArray());

        $repository->findByReference(Argument::any())->shouldBeCalled()->willReturn($definition);
        $denormalizer->denormalize(
            $item,
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule',
            null,
            ['definitionObject' => $definition]
        )->shouldBeCalled()->willReturn($rule);
        $validator->validate($rule)->shouldBeCalled()->willReturn($violations);

        $this->process($item)->shouldBeValidRuleDefinition($definition);
    }

    function it_skips_an_invalid_item(
        $repository,
        $denormalizer,
        $validator,
        RuleInterface $rule,
        ConstraintViolationListInterface $violations
    ) {
        $item = $this->getRuleArray();
        $violations->count()->willReturn(2);
        $violations->rewind()->willReturn(null);
        $violations->valid()->shouldBeCalled();

        $repository->findByReference(Argument::any())->shouldBeCalled()->willReturn(null);
        $denormalizer->denormalize(
            $item,
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule',
            null,
            ['definitionObject' => null]
        )->shouldBeCalled()->willReturn($rule);
        $validator->validate($rule)->shouldBeCalled()->willReturn($violations);

        $this->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')->during('process', [$item]);
    }

    public function getMatchers()
    {
        return [
            'beValidRuleDefinition' => function($subject, $expected) {
                /** @var RuleDefinitionInterface $expected */
                /** @var RuleDefinitionInterface $subject */
                return $subject->getCode() === $expected->getCode() &&
                    $subject->getPriority() === $expected->getPriority() &&
                    $subject->getType() === $expected->getType() &&
                    $subject->getContent() === $expected->getContent();
            },
        ];
    }

    private function getRuleArray()
    {
        return [
                'code' => 'discharge_fr_description',
                'priority' => 100,
            ]
            + $this->getRuleContentArray()
        ;
    }

    private function getRuleContentArray()
    {
        return [
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field' => 'clothing_size', 'operator' => 'NOT LIKE', 'value' => 'XL', 'locale' => 'fr_FR', 'scope' => 'ecommerce'],
            ],
            'actions' => [
                ['type' => 'set_value', 'field' => 'name', 'value' => 'awesome-jacket', 'locale' => 'en_US', 'scope' => 'tablet'],
                ['type' => 'copy_value', 'from_field' => 'description', 'to_field' => 'description', 'from_locale' => 'fr_FR', 'to_locale' => 'fr_CH'],
            ],
        ];
    }
}
