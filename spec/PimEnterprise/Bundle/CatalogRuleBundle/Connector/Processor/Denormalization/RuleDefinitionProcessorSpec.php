<?php

namespace spec\PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition;
use Akeneo\Bundle\RuleEngineBundle\Model\RuleInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
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
            'Akeneo\Bundle\RuleEngineBundle\Model\RuleDefinition',
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule'
        );

        $repository->getIdentifierProperties()->willReturn(['code']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(
            'PimEnterprise\Bundle\CatalogRuleBundle\Connector\Processor\Denormalization\RuleDefinitionProcessor'
        );
    }

    function it_is_an_import_processor()
    {
        $this->shouldHaveType('Pim\Component\Connector\Processor\Denormalization\AbstractProcessor');
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
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule',
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
        RuleInterface $rule
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
            'Akeneo\Bundle\RuleEngineBundle\Model\Rule',
            null,
            ['definitionObject' => null]
        )->shouldBeCalled()->willReturn($rule);
        $validator->validate($rule)->shouldBeCalled()->willReturn($violations);

        $this->shouldThrow(
            new InvalidItemException(": error: invalid value 1, invalid value 2\n", $item, [], 0, null)
        )->during('process', [$item]);
    }

    function getMatchers()
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
