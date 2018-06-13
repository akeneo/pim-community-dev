<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace spec\PimEnterprise\Bundle\ProductAssetBundle\MassUpload;

use Akeneo\Component\Batch\Item\DataInvalidItem;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Step\TaskletInterface;
use PimEnterprise\Bundle\ProductAssetBundle\MassUpload\MassUploadToEntityWithValuesTasklet;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\ProcessedItemList;
use PimEnterprise\Component\ProductAsset\Upload\Processor\AddAssetsTo;
use PimEnterprise\Component\ProductAsset\Upload\Processor\MassUploadIntoEntityWithValuesProcessor;
use PimEnterprise\Component\ProductAsset\Upload\UploadContext;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadToEntityWithValuesTaskletSpec extends ObjectBehavior
{
    function let(
        MassUploadIntoEntityWithValuesProcessor $massUploadToProductProcessor,
        MassUploadIntoEntityWithValuesProcessor $massUploadToProductModelProcessor,
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(
            $massUploadToProductProcessor,
            $massUploadToProductModelProcessor,
            '/tmp/pim/file_storage'
        );
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_mass_upload_to_product_tasklet()
    {
        $this->shouldHaveType(MassUploadToEntityWithValuesTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_mass_upload_files_into_a_product(
        $stepExecution,
        $massUploadToProductProcessor,
        $massUploadToProductModelProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new \SplFileInfo('file_a.jpg'),
            ProcessedItem::STATE_SUCCESS,
            'Reason for success'
        );
        $processedItemList->addItem(
            new \SplFileInfo('file_b.jpg'),
            ProcessedItem::STATE_SKIPPED,
            'Reason to be skipped'
        );
        $processedItemList->addItem(
            new \SplFileInfo('file_c.jpg'),
            ProcessedItem::STATE_ERROR,
            '',
            new \Exception('Exception message')
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_id')->willReturn('42');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');

        $massUploadToProductProcessor->process(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new AddAssetsTo(42, 'asset_collection')
        )->willReturn($processedItemList);
        $massUploadToProductModelProcessor->process(Argument::cetera())->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo('Reason for success')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('variations_not_generated')->shouldBeCalledTimes(1);
        $stepExecution->addWarning(
            'Reason to be skipped',
            [],
            new DataInvalidItem(['filename' => 'file_b.jpg'])
        )->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('error')->shouldBeCalledTimes(1);
        $stepExecution->addError('Exception message')->shouldBeCalledTimes(1);

        $this->execute();
    }

    function it_mass_upload_files_into_a_product_model(
        $stepExecution,
        $massUploadToProductProcessor,
        $massUploadToProductModelProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new \SplFileInfo('file_a.jpg'),
            ProcessedItem::STATE_SUCCESS,
            'Reason for success'
        );
        $processedItemList->addItem(
            new \SplFileInfo('file_b.jpg'),
            ProcessedItem::STATE_SKIPPED,
            'Reason to be skipped'
        );
        $processedItemList->addItem(
            new \SplFileInfo('file_c.jpg'),
            ProcessedItem::STATE_ERROR,
            '',
            new \Exception('Exception message')
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product-model');
        $jobParameters->get('entity_id')->willReturn('42');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');

        $massUploadToProductProcessor->process(Argument::cetera())->shouldNotBeCalled();
        $massUploadToProductModelProcessor->process(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new AddAssetsTo(42, 'asset_collection')
        )->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo('Reason for success')->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('variations_not_generated')->shouldBeCalledTimes(1);
        $stepExecution->addWarning(
            'Reason to be skipped',
            [],
            new DataInvalidItem(['filename' => 'file_b.jpg'])
        )->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('error')->shouldBeCalledTimes(1);
        $stepExecution->addError('Exception message')->shouldBeCalledTimes(1);

        $this->execute();
    }

    function it_throws_an_exception_if_processed_item_is_not_a_file(
        $stepExecution,
        $massUploadToProductProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(new \StdClass(), ProcessedItem::STATE_SUCCESS, 'Reason for success');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_id')->willReturn('42');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');

        $massUploadToProductProcessor->process(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new AddAssetsTo(42, 'asset_collection')
        )->willReturn($processedItemList);

        $this->shouldThrow(\InvalidArgumentException::class)->during('execute');
    }
}
