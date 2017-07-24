<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Item\InvalidItemException;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Comparator\Filter\FilterInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Connector\Processor\Denormalization\ProductModelProcessor;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductModelProcessorSpec extends ObjectBehavior
{
    function let(
        SimpleFactoryInterface $productModelFactory,
        ObjectUpdaterInterface $productModelUpdater,
        IdentifiableObjectRepositoryInterface $productModelRepository,
        ValidatorInterface $validator,
        FilterInterface $productModelFilter,
        ObjectDetacherInterface $objectDetacher
    )
    {
        $this->beConstructedWith(
            $productModelFactory,
            $productModelUpdater,
            $productModelRepository,
            $validator,
            $productModelFilter,
            $objectDetacher
        );
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductModelProcessor::class);
    }

    function it_is_item_processor()
    {
        $this->shouldImplement(ItemProcessorInterface::class);
    }

    function it_is_a_step_aware_execution()
    {
        $this->shouldImplement(StepExecutionAwareInterface::class);
    }

    function it_creates_a_product_model_without_comparision(
        $productModelFactory,
        $productModelUpdater,
        $productModelRepository,
        $validator,
        StepExecution $stepExecution,
        ProductModelInterface $productModel,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList
    ) {
        $productModelData = [
            'identifier' => 'product_model_identifier',
            'family_variant' => 'tshirt',
            'values' => [
                'name' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt',
                ],
                'description' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt super beau',
                ]
            ],
            'categories' => ['tshirt'],
        ];

        $this->setStepExecution($stepExecution);

        $productModelRepository->findOneByIdentifier('product_model_identifier')->willReturn(null);

        $productModelFactory->create()->willReturn($productModel);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(false);

        $productModelUpdater->update($productModel, $productModelData);

        $validator->validate($productModel)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);

        $this->process($productModelData)->shouldReturn($productModel);
    }

    function it_creates_a_product_model_with_comparision(
        $productModelFactory,
        $productModelUpdater,
        $productModelFilter,
        $productModelRepository,
        $validator,
        StepExecution $stepExecution,
        ProductModelInterface $productModel,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList
    ) {
        $productModelData = [
            'identifier' => 'product_model_identifier',
            'family_variant' => 'tshirt',
            'values' => [
                'name' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt',
                ],
                'description' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt super beau',
                ]
            ],
            'categories' => ['tshirt'],
        ];

        $this->setStepExecution($stepExecution);

        $productModelRepository->findOneByIdentifier('product_model_identifier')->willReturn(null);

        $productModelFactory->create()->willReturn($productModel);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);

        $filteredData = [
            'identifier' => 'product_model_identifier',
            'family_variant' => 'tshirt',
            'values' => [
                'name' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt',
                ],
            ],
            'categories' => ['tshirt'],
        ];

        $productModel->getId()->willReturn(40);
        $productModelFilter->filter($productModel, $productModelData)->willReturn($filteredData);

        $productModelUpdater->update($productModel, $filteredData);

        $validator->validate($productModel)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(0);

        $this->process($productModelData)->shouldReturn($productModel);
    }

    function it_skips_product_if_there_is_no_change(
        $productModelFactory,
        $productModelUpdater,
        $productModelFilter,
        $productModelRepository,
        $objectDetacher,
        StepExecution $stepExecution,
        ProductModelInterface $productModel,
        JobParameters $jobParameters
    ) {
        $productModelData = [
            'identifier' => 'product_model_identifier',
            'family_variant' => 'tshirt',
            'values' => [
                'name' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt',
                ],
                'description' => [
                    'locale' => 'fr_FR',
                    'scope' => 'null',
                    'data' => 'T-shirt super beau',
                ]
            ],
            'categories' => ['tshirt'],
        ];

        $this->setStepExecution($stepExecution);

        $productModelRepository->findOneByIdentifier('product_model_identifier')->willReturn(null);

        $productModelFactory->create()->willReturn($productModel);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);

        $productModel->getId()->willReturn(40);
        $productModelFilter->filter($productModel, $productModelData)->willReturn([]);

        $objectDetacher->detach($productModel)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('product_skipped_no_diff')->shouldBeCalled();
        $productModelUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this->process($productModelData)->shouldReturn(null);
    }

    function it_skips_the_product_model_creation_because_product_model_is_invalid(
        $productModelFactory,
        $productModelUpdater,
        $productModelRepository,
        $validator,
        $objectDetacher,
        StepExecution $stepExecution,
        ProductModelInterface $productModel,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList
    ) {
        $this->setStepExecution($stepExecution);

        $productModelRepository->findOneByIdentifier('product_model_identifier')->willReturn(null);

        $productModelFactory->create()->willReturn($productModel);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(false);

        $productModelUpdater->update(Argument::cetera())->shouldBeCalled();

        $validator->validate($productModel)->willReturn($constraintViolationList);
        $constraintViolationList->count()->willReturn(1);
        $constraintViolationList->rewind()->shouldBeCalled();
        $constraintViolationList->valid()->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();
        $objectDetacher->detach($productModel)->shouldBeCalled();

        $this->shouldThrow(InvalidItemException::class)->during('process', [[
            'identifier' => 'product_model_identifier',
        ]]);
    }

    function it_skips_the_product_model_is_it_does_not_have_identifier(
        $productModelRepository,
        StepExecution $stepExecution
    ) {
        $this->setStepExecution($stepExecution);

        $productModelRepository->findOneByIdentifier('product_model_identifier')->willReturn(null);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $this->shouldThrow(InvalidItemException::class)->during('process', [[
            'family_variant' => 'tshirt',
        ]]);
    }
}
