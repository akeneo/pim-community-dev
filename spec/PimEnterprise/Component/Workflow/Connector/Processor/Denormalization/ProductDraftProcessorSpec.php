<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Item\InvalidItemException;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use PimEnterprise\Component\Workflow\Applier\ProductDraftApplierInterface;
use PimEnterprise\Component\Workflow\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraft;
use PimEnterprise\Component\Workflow\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductDraftProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $repository,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ProductDraftBuilderInterface $productDraftBuilder,
        ProductDraftApplierInterface $productDraftApplier,
        ProductDraftRepositoryInterface $productDraftRepo,
        StepExecution $stepExecution,
        AttributeConverterInterface $localizedConverter
    ) {
        $this->beConstructedWith(
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

    function it_creates_a_proposal(
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
        JobInstance $jobInstance,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        $localizedConverter->convertToDefaultFormats($values['converted_values'], [
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
            ->process($values['converted_values'])
            ->shouldReturn($productDraft);
    }

    function it_skips_a_proposal_if_there_is_no_identifier(
        $repository,
        $localizedConverter,
        $stepExecution,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        unset($values['converted_values']['sku']);
        unset($values['converted_localized_values']['sku']);

        $localizedConverter->convertToDefaultFormats($values['converted_values'], [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($values['converted_localized_values']);
        $localizedConverter->getViolations()->willReturn($violationList);;

        $this
            ->shouldThrow(new \InvalidArgumentException('Identifier property "sku" is expected'))
            ->during(
                'process',
                [$values['converted_values']]
            );
    }

    function it_skips_a_proposal_if_product_does_not_exist(
        $repository,
        $stepExecution,
        $localizedConverter,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn(null);

        $values = $this->getValues();

        $localizedConverter->convertToDefaultFormats($values['converted_values'], [
            'decimal_separator' => '.',
            'date_format'       => 'yyyy-MM-dd'
        ])->willReturn($values['converted_localized_values']);
        $localizedConverter->getViolations()->willReturn($violationList);;

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this
            ->shouldThrow(new InvalidItemException(
                'Product "my-sku" does not exist',
                $values['converted_localized_values']
            ))
            ->during(
                'process',
                [$values['converted_values']]
            );
    }

    function it_skips_a_proposal_if_there_is_no_diff_between_product_and_proposal(
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
        $localizedConverter,
        ProductInterface $product,
        ConstraintViolationListInterface $violationList,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        $localizedConverter->convertToDefaultFormats($values['converted_values'], [
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

        $this->process($values['converted_values'])->shouldReturn(null);
    }

    function it_skips_a_proposal_when_product_is_invalid(
        $repository,
        $updater,
        $validator,
        $stepExecution,
        $localizedConverter,
        ProductInterface $product,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('decimalSeparator')->willReturn('.');
        $jobParameters->get('dateFormat')->willReturn('yyyy-MM-dd');

        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        $localizedConverter->convertToDefaultFormats($values['converted_values'], [
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
                [$values['converted_values']]
            );
    }

    function getValues()
    {
        return [
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
