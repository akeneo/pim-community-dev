<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization;

use Akeneo\Pim\Automation\RuleEngine\Component\Command\CreateOrUpdateRuleCommand;
use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor;
use Akeneo\Pim\Automation\RuleEngine\Component\Updater\RuleDefinitionUpdaterInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinitionInterface;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RuleDefinitionProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        AttributeRepositoryInterface $attributeRepository,
        FileStorerInterface $fileStorer
    ) {
        $this->beConstructedWith(
            $repository,
            $denormalizer,
            $validator,
            $ruleDefinitionUpdater,
            $attributeRepository,
            $fileStorer,
            RuleDefinition::class,
            Rule::class
        );

        $repository->getIdentifierProperties()->willReturn(['code']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(RuleDefinitionProcessor::class);
    }

    function it_is_an_import_processor()
    {
        $this->shouldHaveType(AbstractProcessor::class);
    }

    function it_processes_a_new_valid_item(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleInterface $rule
    ) {
        $item = [
            'code'       => 'discharge_fr_description',
            'priority'   => 100,
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
            ],
            'actions'    => [
                ['type'   => 'set_value',
                 'field'  => 'name',
                 'value'  => 'awesome-jacket',
                 'locale' => 'en_US',
                 'scope'  => 'tablet',
                ],
            ],
        ];

        $repository->findOneByIdentifier('discharge_fr_description')->shouldBeCalledOnce()->willReturn(null);
        $denormalizer->denormalize(
            Argument::type('array'),
            Rule::class,
            null,
            ['definitionObject' => null]
        )->shouldBeCalled()->willReturn($rule);
        $validator->validate(Argument::type(CreateOrUpdateRuleCommand::class), null, ['Default', 'import'])
            ->shouldBeCalled()
            ->willReturn(new ConstraintViolationList([]));

        $ruleDefinitionUpdater->fromRule(Argument::type(RuleDefinition::class), $rule)->shouldBeCalled();

        $this->process($item)->shouldBeAnInstanceOf(RuleDefinition::class);
    }

    function it_processes_an_existing_valid_item(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        RuleDefinitionUpdaterInterface $ruleDefinitionUpdater,
        RuleDefinitionInterface $ruleDefinition,
        RuleInterface $rule
    ) {
        $item = [
            'code' => 'discharge_fr_description',
            'priority' => 100,
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
            ],
            'actions' => [
                [
                    'type' => 'set_value',
                    'field' => 'name',
                    'value' => 'awesome-jacket',
                    'locale' => 'en_US',
                    'scope' => 'tablet',
                ],
            ],
        ];

        $repository->findOneByIdentifier('discharge_fr_description')->shouldBeCalledOnce()->willReturn($ruleDefinition);
        $denormalizer->denormalize(
            Argument::type('array'),
            Rule::class,
            null,
            ['definitionObject' => $ruleDefinition]
        )->shouldBeCalled()->willReturn($rule);
        $validator->validate(Argument::type(CreateOrUpdateRuleCommand::class), null, ['Default', 'import'])
                  ->shouldBeCalled()
                  ->willReturn(new ConstraintViolationList([]));

        $ruleDefinitionUpdater->fromRule($ruleDefinition, $rule)->shouldBeCalled();

        $definition = $this->process($item);
        $definition->shouldReturn($ruleDefinition);
    }

    function it_skips_an_invalid_item(
        $repository,
        $denormalizer,
        $validator,
        RuleInterface $rule,
        StepExecution $stepExecution
    ) {
        $item = [
            'code'       => 'discharge_fr_description',
            'priority'   => 100,
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
            ],
            'actions'    => [
                ['type'   => 'set_value',
                 'field'  => 'name',
                 'value'  => 'awesome-jacket',
                 'locale' => 'en_US',
                 'scope'  => 'tablet',
                ],
            ],
        ];
        $violation  = new ConstraintViolation('error', 'error', [], '', '', ['invalid value 1', 'invalid value 2']);
        $violations = new ConstraintViolationList([$violation]);

        $validator->validate(Argument::type(CreateOrUpdateRuleCommand::class),  null, ['Default', 'import'])
            ->shouldBeCalled()
            ->willReturn($violations);

        $repository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();
        $denormalizer->denormalize(Argument::cetera())->shouldNotBeCalled();

        $this->setStepExecution($stepExecution);
        $stepExecution->getSummaryInfo('item_position')->willReturn(1);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }
}
