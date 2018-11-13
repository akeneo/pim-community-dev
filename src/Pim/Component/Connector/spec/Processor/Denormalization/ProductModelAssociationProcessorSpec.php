<?php

namespace spec\Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Comparator\Filter\FilterInterface;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProductModelAssociationProcessorSpec extends ObjectBehavior
{
    function let(
        IdentifiableObjectRepositoryInterface $productRepository,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        StepExecution $stepExecution,
        FilterInterface $productAssocFilter,
        ObjectDetacherInterface $productDetacher
    ) {
        $this->beConstructedWith(
            $productRepository,
            $productUpdater,
            $productValidator,
            $productAssocFilter,
            $productDetacher
        );

        $this->setStepExecution($stepExecution);
    }

    function it_is_a_processor()
    {
        $this->shouldImplement('Akeneo\Component\Batch\Item\ItemProcessorInterface');
        $this->shouldImplement('Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_updates_an_existing_product_model(
        $productRepository,
        $productUpdater,
        $productValidator,
        $productAssocFilter,
        $stepExecution,
        ProductModelInterface $productModel,
        AssociationInterface $association,
        ConstraintViolationListInterface $violationList,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);

        $productRepository->getIdentifierProperties()->willReturn(['code']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($productModel);

        $convertedData = [
            'code'   => 'tshirt',
            'values'       => ['some_value'],
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $filteredData = [
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        unset($filteredData['associations']['XSELL']['groups']);
        $productAssocFilter->filter($productModel, $convertedData)
            ->shouldBeCalled()
            ->willReturn($filteredData);

        $productUpdater
            ->update($productModel, $filteredData)
            ->shouldBeCalled();

        $productModel->getAssociations()->willReturn([$association]);
        $productValidator
            ->validate($association)
            ->willReturn($violationList);

        $this
            ->process($convertedData)
            ->shouldReturn($productModel);
    }

    function it_skips_a_product_model_when_update_fails(
        $productRepository,
        $productUpdater,
        $productAssocFilter,
        $stepExecution,
        $productDetacher,
        ProductModelInterface $productModel,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $productRepository->getIdentifierProperties()->willReturn(['code']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($productModel);

        $convertedData = [
            'code'   => 'tshirt',
            'values'       => ['some_value'],
            'associations' => [
                'NOT_FOUND' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $filteredData = [
            'associations' => [
                'NOT_FOUND' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $productAssocFilter->filter($productModel, $convertedData)
            ->shouldBeCalled()
            ->willReturn($filteredData);

        $productUpdater
            ->update($productModel, $filteredData)
            ->willThrow(new InvalidPropertyException('associations', 'value', 'className', 'association does not exists'));

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->setStepExecution($stepExecution);

        $productDetacher->detach($productModel)->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$convertedData]
            );
    }

    function it_skips_a_product_when_association_is_invalid(
        $productRepository,
        $productUpdater,
        $productValidator,
        $productAssocFilter,
        $stepExecution,
        $productDetacher,
        AssociationInterface $association,
        ProductModelInterface $productModel,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();
        $jobParameters->get('enabledComparison')->willReturn(true);

        $productRepository->getIdentifierProperties()->willReturn(['code']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($productModel);

        $convertedData = [
            'code'   => 'tshirt',
            'values'       => ['some_value'],
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $filteredData = [
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TS']
                ]
            ]
        ];

        $productAssocFilter->filter($productModel, $convertedData)
            ->shouldBeCalled()
            ->willReturn($filteredData);

        $productUpdater
            ->update($productModel, $filteredData)
            ->shouldBeCalled();

        $violation = new ConstraintViolation('There is a small problem with option code', 'foo', [], 'bar', 'code', 'mycode');
        $violations = new ConstraintViolationList([$violation]);
        $productModel->getAssociations()->willReturn([$association]);
        $productValidator
            ->validate($association)
            ->willReturn($violations);

        $stepExecution->incrementSummaryInfo('skip')->shouldBeCalled();
        $this->setStepExecution($stepExecution);

        $productDetacher->detach($productModel)->shouldBeCalled();

        $this
            ->shouldThrow('Akeneo\Component\Batch\Item\InvalidItemException')
            ->during(
                'process',
                [$convertedData]
            );
    }

    function it_skips_a_product_when_there_is_nothing_to_update(
        $productRepository,
        $productUpdater,
        $productAssocFilter,
        $stepExecution,
        $productDetacher,
        ProductModelInterface $productModel,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(true);

        $productRepository->getIdentifierProperties()->willReturn(['code']);
        $productRepository->findOneByIdentifier(Argument::any())->willReturn($productModel);

        $convertedData = [
            'code'   => 'tshirt',
            'values'       => ['some_value'],
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $filteredData = [
            'associations' => [
                'XSELL' => [
                    'groups'  => ['akeneo_tshirt', 'oro_tshirt'],
                    'product' => ['AKN_TS', 'ORO_TSH']
                ]
            ]
        ];

        $productAssocFilter->filter($productModel, $convertedData)
            ->shouldBeCalled()
            ->willReturn([]);

        $productUpdater
            ->update($productModel, $filteredData)
            ->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('product_model_skipped_no_diff')->shouldBeCalled();
        $this->setStepExecution($stepExecution);

        $productDetacher->detach($productModel)->shouldBeCalled();

        $this->process($convertedData)
            ->shouldReturn(null);
    }

    function it_skips_a_product_when_there_is_no_association_to_update(
        $productRepository,
        $productUpdater,
        $productAssocFilter,
        $stepExecution,
        JobParameters $jobParameters
    ) {
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('enabledComparison')->willReturn(false);

        $productRepository->getIdentifierProperties()->shouldNotBeCalled();
        $productRepository->findOneByIdentifier(Argument::any())->shouldNotBeCalled();

        $convertedData = [
            'code' => 'tshirt',
            'associations' => []
        ];

        $productAssocFilter->filter(Argument::any())->shouldNotBeCalled();
        $productUpdater->update(Argument::any())->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('product_model_skipped_no_associations')->shouldBeCalled();
        $this->setStepExecution($stepExecution);
        $this->process($convertedData)->shouldReturn(null);
    }
}
