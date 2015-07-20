<?php

namespace spec\PimEnterprise\Component\Workflow\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Entity\JobExecution;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use PimEnterprise\Bundle\WorkflowBundle\Applier\ProductDraftApplierInterface;
use PimEnterprise\Bundle\WorkflowBundle\Builder\ProductDraftBuilderInterface;
use PimEnterprise\Bundle\WorkflowBundle\Model\ProductDraft;
use PimEnterprise\Bundle\WorkflowBundle\Repository\ProductDraftRepositoryInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\ValidatorInterface;

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
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $arrayConverter,
            $repository,
            $updater,
            $validator,
            $productDraftBuilder,
            $productDraftApplier,
            $productDraftRepo
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_configurable_step_execution_aware_processor()
    {
        $this->shouldBeAnInstanceOf('Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_has_no_extra_configuration()
    {
        $this->getConfigurationFields()->shouldReturn([]);
    }

    function it_creates_a_proposal(
        $arrayConverter,
        $repository,
        $updater,
        $validator,
        $productDraftBuilder,
        $stepExecution,
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

        $updater
            ->update($product, $values['converted_values'])
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
        ProductInterface $product
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        unset($values['original_values']['sku']);
        unset($values['converted_values']['sku']);

        $arrayConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $this
            ->shouldThrow(new \InvalidArgumentException('Identifier property "sku" is expected'))
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_proposal_if_product_does_not_exist($arrayConverter, $repository, $stepExecution)
    {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn(null);

        $values = $this->getValues();

        $arrayConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

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

        $updater
            ->update($product, $values['converted_values'])
            ->shouldBeCalled();

        $validator
            ->validate($product)
            ->willReturn($violationList);

        $productDraftBuilder->build($product, 'csv_product_proposal_import')->willReturn(null);

        $jobInstance->getCode()->willReturn('csv_product_proposal_import');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('proposal_skipped')->shouldBeCalled();

        $this
            ->shouldThrow(new InvalidItemException('No diff between current product and this proposal', $values['original_values']))
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function it_skips_a_proposal_when_product_is_invalid(
        $arrayConverter,
        $repository,
        $updater,
        $validator,
        $stepExecution,
        ProductInterface $product,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $repository->getIdentifierProperties()->willReturn(['sku']);
        $repository->findOneByIdentifier('my-sku')->willReturn($product);

        $values = $this->getValues();

        $arrayConverter
            ->convert($values['original_values'])
            ->willReturn($values['converted_values']);

        $updater
            ->update($product, $values['converted_values'])
            ->willThrow(new \InvalidArgumentException('A locale must be provided to create a value for the localizable attribute name'));

        $violation = new ConstraintViolation('Error', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $validator->validate($product)
            ->willReturn($violations);

        $jobInstance->getCode()->willReturn('csv_product_proposal_import');
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Bundle\BatchBundle\Item\InvalidItemException')
            ->during(
                'process',
                [$values['original_values']]
            );
    }

    function getValues()
    {
        return [
            'original_values' => [
                'sku'                        => 'my-sku',
                'main_color'                 => 'white',
                'description-fr_FR-ecommerce'=> '<p>description</p>',
                'description-en_US-ecommerce'=> '<p>description</p>'
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
                ]
            ]
        ];
    }
}
