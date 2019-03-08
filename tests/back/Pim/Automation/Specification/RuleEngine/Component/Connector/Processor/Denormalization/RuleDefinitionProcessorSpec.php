<?php

namespace Specification\Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization;

use Akeneo\Pim\Automation\RuleEngine\Component\Connector\Processor\Denormalization\RuleDefinitionProcessor;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\Rule;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Tool\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Processor\Denormalization\AbstractProcessor;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RuleDefinitionProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        DenormalizerInterface $denormalizer,
        ValidatorInterface $validator,
        ObjectDetacherInterface $detacher
    ) {
        $this->beConstructedWith(
            $repository,
            $denormalizer,
            $validator,
            $detacher,
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
        $repository,
        $denormalizer,
        $validator,
        RuleInterface $rule,
        ConstraintViolationListInterface $violations
    ) {
        $item = [
            'code'       => 'discharge_fr_description',
            'priority'   => 100,
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field'    => 'clothing_size',
                 'operator' => 'NOT LIKE',
                 'value'    => 'XL',
                 'locale'   => 'fr_FR',
                 'scope'    => 'ecommerce',
                ],
            ],
            'actions'    => [
                ['type'   => 'set_value',
                 'field'  => 'name',
                 'value'  => 'awesome-jacket',
                 'locale' => 'en_US',
                 'scope'  => 'tablet',
                ],
                ['type'        => 'copy_value',
                 'from_field'  => 'description',
                 'to_field'    => 'description',
                 'from_locale' => 'fr_FR',
                 'to_locale'   => 'fr_CH',
                ],
            ],
        ];

        $repository->findOneByIdentifier(Argument::any())->shouldBeCalled()->willReturn(null);
        $denormalizer->denormalize(
            $item,
           Rule::class,
            null,
            ['definitionObject' => null]
        )->shouldBeCalled()->willReturn($rule);
        $validator->validate($rule)->shouldBeCalled()->willReturn($violations);

        $rule->getCode()->willReturn('discharge_fr_description');
        $rule->getPriority()->willReturn(100);
        $rule->getType()->willReturn('product');
        $rule->getContent()->willReturn(
            [
                'conditions' => [
                    ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                    ['field'    => 'clothing_size',
                     'operator' => 'NOT LIKE',
                     'value'    => 'XL',
                     'locale'   => 'fr_FR',
                     'scope'    => 'ecommerce',
                    ],
                ],
                'actions'    => [
                    ['type'   => 'set_value',
                     'field'  => 'name',
                     'value'  => 'awesome-jacket',
                     'locale' => 'en_US',
                     'scope'  => 'tablet',
                    ],
                    ['type'        => 'copy_value',
                     'from_field'  => 'description',
                     'to_field'    => 'description',
                     'from_locale' => 'fr_FR',
                     'to_locale'   => 'fr_CH',
                    ],
                ],
            ]
        );

        $definition = new RuleDefinition();
        $definition->setCode('discharge_fr_description');
        $definition->setPriority(100);
        $definition->setType('product');
        $definition->setContent(
            [
                'conditions' => [
                    ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                    ['field'    => 'clothing_size',
                     'operator' => 'NOT LIKE',
                     'value'    => 'XL',
                     'locale'   => 'fr_FR',
                     'scope'    => 'ecommerce',
                    ],
                ],
                'actions'    => [
                    ['type'   => 'set_value',
                     'field'  => 'name',
                     'value'  => 'awesome-jacket',
                     'locale' => 'en_US',
                     'scope'  => 'tablet',
                    ],
                    ['type'        => 'copy_value',
                     'from_field'  => 'description',
                     'to_field'    => 'description',
                     'from_locale' => 'fr_FR',
                     'to_locale'   => 'fr_CH',
                    ],
                ],
            ]
        );

        $this->process($item)->shouldBeValidRuleDefinition($definition);
    }

    function it_processes_an_existing_valid_item(
        $repository,
        $denormalizer,
        $validator,
        RuleInterface $rule,
        ConstraintViolationListInterface $violations
    ) {
        $item = [
            'code'       => 'discharge_fr_description',
            'priority'   => 100,
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field'    => 'clothing_size',
                 'operator' => 'NOT LIKE',
                 'value'    => 'XL',
                 'locale'   => 'fr_FR',
                 'scope'    => 'ecommerce',
                ],
            ],
            'actions'    => [
                ['type'   => 'set_value',
                 'field'  => 'name',
                 'value'  => 'awesome-jacket',
                 'locale' => 'en_US',
                 'scope'  => 'tablet',
                ],
                ['type'        => 'copy_value',
                 'from_field'  => 'description',
                 'to_field'    => 'description',
                 'from_locale' => 'fr_FR',
                 'to_locale'   => 'fr_CH',
                ],
            ],
        ];

        $rule->getCode()->willReturn('discharge_fr_description');
        $rule->getPriority()->willReturn(100);
        $rule->getType()->willReturn('product');
        $rule->getContent()->willReturn(
            [
                'conditions' => [
                    ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                    ['field'    => 'clothing_size',
                     'operator' => 'NOT LIKE',
                     'value'    => 'XL',
                     'locale'   => 'fr_FR',
                     'scope'    => 'ecommerce',
                    ],
                ],
                'actions'    => [
                    ['type'   => 'set_value',
                     'field'  => 'name',
                     'value'  => 'awesome-jacket',
                     'locale' => 'en_US',
                     'scope'  => 'tablet',
                    ],
                    ['type'        => 'copy_value',
                     'from_field'  => 'description',
                     'to_field'    => 'description',
                     'from_locale' => 'fr_FR',
                     'to_locale'   => 'fr_CH',
                    ],
                ],
            ]
        );

        $definition = new RuleDefinition();
        $definition->setCode('discharge_fr_description');
        $definition->setPriority(100);
        $definition->setType('product');
        $definition->setContent(
            [
                'conditions' => [
                    ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                    ['field'    => 'clothing_size',
                     'operator' => 'NOT LIKE',
                     'value'    => 'XL',
                     'locale'   => 'fr_FR',
                     'scope'    => 'ecommerce',
                    ],
                ],
                'actions'    => [
                    ['type'   => 'set_value',
                     'field'  => 'name',
                     'value'  => 'awesome-jacket',
                     'locale' => 'en_US',
                     'scope'  => 'tablet',
                    ],
                    ['type'        => 'copy_value',
                     'from_field'  => 'description',
                     'to_field'    => 'description',
                     'from_locale' => 'fr_FR',
                     'to_locale'   => 'fr_CH',
                    ],
                ],
            ]
        );

        $repository->findOneByIdentifier(Argument::any())->shouldBeCalled()->willReturn($definition);
        $denormalizer->denormalize(
            $item,
           Rule::class,
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
        StepExecution $stepExecution
    ) {
        $item = [
            'code'       => 'discharge_fr_description',
            'priority'   => 100,
            'conditions' => [
                ['field' => 'sku', 'operator' => 'LIKE', 'value' => 'foo'],
                ['field'    => 'clothing_size',
                 'operator' => 'NOT LIKE',
                 'value'    => 'XL',
                 'locale'   => 'fr_FR',
                 'scope'    => 'ecommerce',
                ],
            ],
            'actions'    => [
                ['type'   => 'set_value',
                 'field'  => 'name',
                 'value'  => 'awesome-jacket',
                 'locale' => 'en_US',
                 'scope'  => 'tablet',
                ],
                ['type'        => 'copy_value',
                 'from_field'  => 'description',
                 'to_field'    => 'description',
                 'from_locale' => 'fr_FR',
                 'to_locale'   => 'fr_CH',
                ],
            ],
        ];
        $violation  = new ConstraintViolation('error', 'error', [], '', '', ['invalid value 1', 'invalid value 2']);
        $violations = new ConstraintViolationList([$violation]);

        $repository->findOneByIdentifier(Argument::any())->shouldBeCalled()->willReturn(null);
        $denormalizer->denormalize(
            $item,
           Rule::class,
            null,
            ['definitionObject' => null]
        )->shouldBeCalled()->willReturn($rule);
        $validator->validate($rule)->shouldBeCalled()->willReturn($violations);

        $this->setStepExecution($stepExecution);
        $stepExecution->getSummaryInfo('item_position')->willReturn(1);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }

    function getMatchers(): array
    {
        return [
            'beValidRuleDefinition' => function ($subject, $expected) {
                return $subject->getCode() === $expected->getCode() &&
                    $subject->getPriority() === $expected->getPriority() &&
                    $subject->getType() === $expected->getType() &&
                    $subject->getContent() === $expected->getContent();
            },
        ];
    }
}
