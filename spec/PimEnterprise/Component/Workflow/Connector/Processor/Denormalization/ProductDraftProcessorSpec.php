<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Applier\ProductDraftApplierInterface;
use PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDraftProcessorSpec extends ObjectBehavior
{
    function let(
        StandardArrayConverterInterface $arrayConverter,
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ProductDraftBuilderInterface $productDraftBuilder,
        ProductDraftApplierInterface $productDraftApplier,
        ProductDraftRepositoryInterface $productDraftRepo,
        StepExecution $stepExecution,
        LocalizedAttributeConverterInterface $localizedConverter
    ) {
        $this->beConstructedWith(
            $arrayConverter,
            $repository,
            $updater,
            $validator,
            $productDraftBuilder,
            $productDraftApplier,
            $productDraftRepo,
            $localizedConverter
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Component\Batch\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_has_decimal_separator_and_date_format_configuration()
    {
        $this->getConfigurationFields()->shouldReturn([
            'decimalSeparator' => [
                'type'    => 'choice',
                'options' => [
                    'label' => 'pim_connector.import.decimalSeparator.label',
                    'help'  => 'pim_connector.import.decimalSeparator.help'
                ]
            ],
            'dateFormat' => [
                'type'    => 'choice',
                'options' => [
                    'label' => 'pim_connector.import.dateFormat.label',
                    'help'  => 'pim_connector.import.dateFormat.help'
                ]
            ]
        ]);
    }

    function it_creates_a_proposal(
        $arrayConverter,
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        ProductDraft $productDraft,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        $arrayConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $localizedConverter->convertLocalizedToDefaultValues($values['converted_values'], [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($values['converted_localized_values']);
        $localizedConverter->getViolations()->willReturn($violationList);;

        $updater
            ->update($product, $values['converted_localized_values'])
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, 'csv_product_proposal_import')->willReturn($productDraft);

        $jobInstance->getCode()->willReturn('csv_product_proposal_import');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);

        $this
            ->process($values['original_values'])
            ->shouldReturn($productDraft);
    }

    function it_skips_a_proposal_if_there_is_no_identifier(
        $arrayConverter,
        $repository,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        unset($values['original_values']['sku']);
        unset($values['converted_values']['sku']);
        unset($values['converted_localized_values']['sku']);

        $arrayConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $localizedConverter->convertLocalizedToDefaultValues($values['converted_values'], [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($values['converted_localized_values']);
        $localizedConverter->getViolations()->willReturn($violationList);;

        $this
            ->shouldThrow(new \InvalidArgumentException('Identifier property "sku" is expected'))
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_proposal_if_product_does_not_exist(
        $arrayConverter,
        $repository,
        $stepExecution,
        $localizedConverter,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn(null);

        $values = $this->getValues();

        $arrayConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $localizedConverter->convertLocalizedToDefaultValues($values['converted_values'], [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($values['converted_localized_values']);
        $localizedConverter->getViolations()->willReturn($violationList);;

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this
            ->shouldThrow(new InvalidItemException('Product "my-sku" does not exist', $values['original_values']))
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_proposal_if_there_is_no_diff_between_product_and_proposal(
        $arrayConverter,
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        $arrayConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $localizedConverter->convertLocalizedToDefaultValues($values['converted_values'], [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($values['converted_localized_values']);
        $localizedConverter->getViolations()->willReturn($violationList);;

        $updater
            ->update($product, $values['converted_localized_values'])
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, 'csv_product_proposal_import')->willReturn(null);

        $jobInstance->getCode()->willReturn('csv_product_proposal_import');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('proposal_skipped')->shouldBeCalled();

        $this->process($values['original_values'])->shouldReturn(null);
    }

    function it_skips_a_proposal_when_product_is_invalid(
        $arrayConverter,
        $repository,
        $updater,
        $validator,
        $stepExecution,
        $localizedConverter,
        ProductInterface $product,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ConstraintViolationListInterface $violationList
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        $arrayConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $localizedConverter->convertLocalizedToDefaultValues($values['converted_values'], [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($values['converted_localized_values']);
        $localizedConverter->getViolations()->willReturn($violationList);;

        $updater
            ->update($product, $values['converted_localized_values'])
            ->willThrow(new \InvalidArgumentException('A locale must be provided to create a value for the localizable attribute name'));

        $violation  = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($product)
            ->willReturn($violations);

        $jobInstance->getCode()->willReturn('csv_product_proposal_import');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function getValues()
    {
        return [
            'original_values'  => [
                'sku'                        => 'my-sku',
                'main_color'                 => 'white',
                'description-fr_FR-ecommerce'=> '<p>description</p>',
                'description-en_US-ecommerce'=> '<p>description</p>',
                'release_date'               => '19/08/1977',
                'price-EUR'                  => '10,25',
                'price-USD'                  => '11,25',
            ],
            'converted_values' => [
                'sku'          => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data' => 'my-sku'
                    ]
                ],
                'main_color'   => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   =>'white'
                    ]
                ],
                'description'  => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>'
                    ],
                ],
                'release_date' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '19/08/1977'
                    ]
                ],
                'price'        => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            [
                                'currency' => 'EUR',
                                'data'     => '10,25'
                            ],
                            [
                                'currency' => 'USD',
                                'data'     => '11,5'
                            ],
                        ]
                    ]
                ],
            ],
            'converted_localized_values' => [
                'sku'          => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data' => 'my-sku'
                    ]
                ],
                'main_color'   => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   =>'white'
                    ]
                ],
                'description'  => [
                    [
                        'locale' => 'fr_FR',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>'
                    ],
                    [
                        'locale' => 'en_US',
                        'scope'  => 'ecommerce',
                        'data'   => '<p>description</p>'
                    ],
                ],
                'release_date' => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => '1977-08-19'
                    ]
                ],
                'price'        => [
                    [
                        'locale' => null,
                        'scope'  => null,
                        'data'   => [
                            [
                                'currency' => 'EUR',
                                'data'     => '10.25'
                            ],
                            [
                                'currency' => 'USD',
                                'data'     => '11.5'
                            ],
                        ]
                    ]
                ],
            ]
        ];
    }
}
