<?php

namespace Specification\Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Denormalization;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\DTO\SelectOptionDetails;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\CountSelectOptions;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Infrastructure\Connector\Processor\Denormalization\SelectOptionDetailsProcessor;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SelectOptionDetailsProcessorSpec extends ObjectBehavior
{
    function let(
        SelectOptionCollectionRepository $selectOptionCollectionRepository,
        ValidatorInterface $validator,
        CountSelectOptions $countSelectOptions,
        StepExecution $stepExecution
    ) {
        $selectOptionCollectionRepository
            ->getByColumn('nutrition', ColumnCode::fromString('ingredient'))
            ->willReturn(
                SelectOptionCollection::fromNormalized(
                    [
                        [
                            'code' => 'sugar',
                            'labels' => [
                                'en_US' => 'SUUgar',
                                'fr_FR' => 'Sucre',
                            ],
                        ],
                        [
                            'code' => 'salt',
                            'labels' => [],
                        ],
                    ]
                )
            );
        $countSelectOptions->forAttributeAndColumn('nutrition', ColumnCode::fromString('ingredient'))
            ->willReturn(5);

        $this->beConstructedWith($selectOptionCollectionRepository, $validator, $countSelectOptions);
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
        $this->shouldImplement(StepExecutionAwareInterface::class);
        $this->shouldHaveType(SelectOptionDetailsProcessor::class);
    }

    function it_processes_a_new_select_option_details(ValidatorInterface $validator)
    {
        $validator->validate(Argument::type(SelectOptionDetails::class))->shouldBeCalled()->willReturn(
            new ConstraintViolationList([])
        );

        $this->process(
            [
                'attribute' => 'nutrition',
                'column' => 'ingredient',
                'code' => 'pepper',
                'labels' => [
                    'en_US' => 'Pepper',
                    'fr_FR' => 'Poivre',
                ],
            ]
        )->shouldBeLike(
            new SelectOptionDetails(
                'nutrition',
                'ingredient',
                'pepper',
                [
                    'en_US' => 'Pepper',
                    'fr_FR' => 'Poivre',
                ]
            )
        );
    }

    function it_throws_an_exception_when_code_is_not_defined()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'process',
            [
                [
                    'attribute' => 'nutrition',
                    'column' => 'ingredient',
                    'labels' => [
                        'en_US' => 'Sugar',
                        'es_ES' => 'Azúcar',
                    ],
                ],
            ]
        );
    }

    function it_throws_an_exception_when_column_is_not_defined()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'process',
            [
                [
                    'attribute' => 'nutrition',
                    'code' => 'sugar',
                    'labels' => [
                        'en_US' => 'Sugar',
                        'es_ES' => 'Azúcar',
                    ],
                ],
            ]
        );
    }

    function it_throws_an_exception_when_attribute_is_not_defined()
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during(
            'process',
            [
                [
                    'column' => 'ingredient',
                    'code' => 'sugar',
                    'labels' => [
                        'en_US' => 'Sugar',
                        'es_ES' => 'Azúcar',
                    ],
                ],
            ]
        );
    }

    function it_processes_a_select_option_details_without_label(ValidatorInterface $validator)
    {
        $validator->validate(Argument::type(SelectOptionDetails::class))->shouldBeCalled()->willReturn(
            new ConstraintViolationList([])
        );
        $this->process(
            [
                'attribute' => 'nutrition',
                'column' => 'ingredient',
                'code' => 'pepper',
            ]
        )->shouldBeLike(
            new SelectOptionDetails(
                'nutrition',
                'ingredient',
                'pepper',
                []
            )
        );
    }

    function it_merges_the_labels_of_an_existing_select_option(ValidatorInterface $validator)
    {
        $validator->validate(Argument::type(SelectOptionDetails::class))->shouldBeCalled()->willReturn(
            new ConstraintViolationList([])
        );
        $this->process(
            [
                'attribute' => 'nutrition',
                'column' => 'ingredient',
                'code' => 'sugar',
                'labels' => [
                    'en_US' => 'Sugar',
                    'es_ES' => 'Azúcar',
                ],
            ]
        )->shouldBeLike(
            new SelectOptionDetails(
                'nutrition',
                'ingredient',
                'sugar',
                [
                    'en_US' => 'Sugar',
                    'es_ES' => 'Azúcar',
                    'fr_FR' => 'Sucre',
                ]
            )
        );
    }

    function it_skips_an_invalid_item(
        ValidatorInterface $validator,
        StepExecution $stepExecution,
        ConstraintViolationInterface $violation
    ) {
        $item = [
            'attribute' => 'nutrition',
            'column' => 'ingredient',
            'code' => 'invalid code',
            'labels' => [],
        ];
        $validator->validate(Argument::type(SelectOptionDetails::class))->shouldBeCalled()->willReturn(
            new ConstraintViolationList([$violation->getWrappedObject()])
        );

        $stepExecution->getSummaryInfo('item_position')->willReturn(7);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->shouldThrow(InvalidItemException::class)->during('process', [$item]);
    }

    function it_skips_the_item_when_the_maximum_number_of_options_is_reached(
        SelectOptionCollectionRepository $selectOptionCollectionRepository,
        CountSelectOptions $countSelectOptions,
        ValidatorInterface $validator,
        StepExecution $stepExecution
    ) {
        $selectOptionCollectionRepository
            ->getByColumn('nutrition', ColumnCode::fromString('score'))
            ->willReturn(SelectOptionCollection::fromNormalized([
                [
                    'code' => 'existing_option',
                    'labels' => [],
                ],
            ]));
        $countSelectOptions->forAttributeAndColumn('nutrition', ColumnCode::fromString('score'))
            ->willReturn(19998);
        $validator->validate(Argument::type(SelectOptionDetails::class))->shouldBeCalled()->willReturn(
            new ConstraintViolationList([])
        );

        $item1 = [
            'attribute' => 'nutrition',
            'column' => 'score',
            'code' => 'zzz',
            'labels' => [],
        ];
        $this->process($item1);

        $item2 = [
            'attribute' => 'nutrition',
            'column' => 'score',
            'code' => 'zzzz',
            'labels' => [],
        ];
        $this->process($item2);

        $item3 = [
            'attribute' => 'nutrition',
            'column' => 'score',
            'code' => 'zzzzz',
            'labels' => [],
        ];
        $stepExecution->getSummaryInfo('item_position')->willReturn(7);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->shouldThrow(InvalidItemException::class)->during('process', [$item3]);

        $updatedItem = [
            'attribute' => 'nutrition',
            'column' => 'score',
            'code' => 'existing_option',
            'labels' => [],
        ];
        $this->process($updatedItem);
    }
}
