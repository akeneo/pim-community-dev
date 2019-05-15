<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\MediaStorer;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Comparator\Filter\FilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductModel\Filter\ProductModelAttributeFilter;
use Akeneo\Pim\Enrichment\Component\Product\Connector\Processor\Denormalizer\ProductModelProcessor;
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
        ObjectDetacherInterface $objectDetacher,
        ProductModelAttributeFilter $attributeFilter,
        MediaStorer $mediaStorer
    ) {
        $this->beConstructedWith(
            $productModelFactory,
            $productModelUpdater,
            $productModelRepository,
            $validator,
            $productModelFilter,
            $objectDetacher,
            $attributeFilter,
            $mediaStorer,
            'root_product_model'
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
        $attributeFilter,
        $mediaStorer,
        StepExecution $stepExecution,
        ProductModelInterface $productModel,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList
    ) {
        $productModelData = [
            'code' => 'product_model_code',
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

        $attributeFilter->filter($productModelData)->willReturn($productModelData);

        $mediaStorer->store($productModelData['values'])->willReturn($productModelData['values']);

        $this->setStepExecution($stepExecution);

        $productModelRepository->findOneByIdentifier('product_model_code')->willReturn(null);

        $productModelFactory->create()->willReturn($productModel);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(false);

        $productModelUpdater->update($productModel, [
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
        ]);

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
        $attributeFilter,
        $mediaStorer,
        StepExecution $stepExecution,
        ProductModelInterface $productModel,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList
    ) {
        $productModelData = [
            'code' => 'product_model_code',
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

        $attributeFilter->filter($productModelData)->willReturn($productModelData);

        $productModelRepository->findOneByIdentifier('product_model_code')->willReturn(null);

        $productModelFactory->create()->willReturn($productModel);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);

        $filteredData = [
            'code' => 'product_model_code',
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
        $productModelFilter->filter($productModel, [
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
        ])->willReturn($filteredData);

        $mediaStorer->store($filteredData['values'])->willReturn($filteredData['values']);

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
        $attributeFilter,
        StepExecution $stepExecution,
        ProductModelInterface $productModel,
        JobParameters $jobParameters
    ) {
        $productModelData = [
            'code' => 'product_model_code',
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

        $attributeFilter->filter($productModelData)->willReturn($productModelData);

        $productModelRepository->findOneByIdentifier('product_model_code')->willReturn(null);

        $productModelFactory->create()->willReturn($productModel);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);

        $productModel->getId()->willReturn(40);
        $productModelFilter->filter($productModel, [
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
        ])->willReturn([]);

        $objectDetacher->detach($productModel)->shouldBeCalled();

        $stepExecution->incrementSummaryInfo('product_model_skipped_no_diff')->shouldBeCalled();
        $productModelUpdater->update(Argument::cetera())->shouldNotBeCalled();

        $this->process($productModelData)->shouldReturn(null);
    }

    function it_skips_the_product_model_creation_because_product_model_is_invalid(
        $productModelFactory,
        $productModelUpdater,
        $productModelRepository,
        $validator,
        $objectDetacher,
        $attributeFilter,
        StepExecution $stepExecution,
        ProductModelInterface $productModel,
        JobParameters $jobParameters,
        ConstraintViolationListInterface $constraintViolationList
    ) {
        $this->setStepExecution($stepExecution);

        $attributeFilter->filter(['code' => 'product_model_code'])->willReturn(['code' => 'product_model_code']);

        $productModelRepository->findOneByIdentifier('product_model_code')->willReturn(null);

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
            'code' => 'product_model_code',
        ]]);
    }

    function it_skips_the_product_model_is_it_does_not_have_code(
        $productModelRepository,
        StepExecution $stepExecution
    ) {
        $this->setStepExecution($stepExecution);

        $productModelRepository->findOneByIdentifier('product_model_code')->willReturn(null);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $this->shouldThrow(InvalidItemException::class)->during('process', [[
            'family_variant' => 'tshirt',
        ]]);
    }

    function it_only_processes_the_root_product_model(
        $productModelFactory,
        $productModelUpdater,
        $productModelRepository,
        $validator,
        $productModelFilter,
        $objectDetacher,
        $attributeFilter,
        $mediaStorer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $productModelFactory,
            $productModelUpdater,
            $productModelRepository,
            $validator,
            $productModelFilter,
            $objectDetacher,
            $attributeFilter,
            $mediaStorer,
            'root_product_model'
        );

        $this->setStepExecution($stepExecution);

        $stepExecution->incrementSummaryInfo('skipped_in_root_product_model')->shouldBeCalled();

        $this->process([
            'code' => 'product_model_code',
            'family_variant' => 'tshirt',
            'parent' => 'parent'
        ])->shouldReturn(null);
    }

    function it_only_processes_the_product_model(
        $productModelFactory,
        $productModelUpdater,
        $productModelRepository,
        $validator,
        $productModelFilter,
        $objectDetacher,
        $attributeFilter,
        $mediaStorer,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $productModelFactory,
            $productModelUpdater,
            $productModelRepository,
            $validator,
            $productModelFilter,
            $objectDetacher,
            $attributeFilter,
            $mediaStorer,
            'sub_product_model'
        );

        $this->setStepExecution($stepExecution);

        $stepExecution->incrementSummaryInfo('skipped_in_sub_product_model')->shouldBeCalled();

        $this->process([
            'code' => 'product_model_code',
            'family_variant' => 'tshirt',
            'parent' => ''
        ])->shouldReturn(null);
    }
}
