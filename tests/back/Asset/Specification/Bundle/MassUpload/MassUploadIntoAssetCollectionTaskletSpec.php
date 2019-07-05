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

namespace Specification\Akeneo\Asset\Bundle\MassUpload\MassUpload;

use Akeneo\Asset\Bundle\MassUpload\MassUploadIntoAssetCollectionTasklet;
use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\ProcessedItemList;
use Akeneo\Asset\Component\Upload\UploadContext;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Asset\Component\Upload\MassUpload\EntityToAddAssetsInto;
use Akeneo\Asset\Component\Upload\MassUpload\MassUploadIntoAssetCollectionProcessor;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadIntoAssetCollectionTaskletSpec extends ObjectBehavior
{
    function let(
        MassUploadIntoAssetCollectionProcessor $massUploadToProductProcessor,
        MassUploadIntoAssetCollectionProcessor $massUploadToProductModelProcessor,
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
        $this->shouldHaveType(MassUploadIntoAssetCollectionTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_mass_uploads_files_into_a_product(
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

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_identifier')->willReturn('foobar');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');
        $jobParameters->get('imported_file_names')->willReturn(['car.png']);

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto('foobar', 'asset_collection'),
            ['car.png']
        )->willReturn($processedItemList);
        $massUploadToProductModelProcessor->applyMassUpload(Argument::cetera())->shouldNotBeCalled();

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('Reason for success')->shouldBeCalled();

        $this->execute();
    }

    function it_mass_uploads_files_into_a_product_model(
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

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product_model');
        $jobParameters->get('entity_identifier')->willReturn('foobar');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');
        $jobParameters->get('imported_file_names')->willReturn(['car.png']);

        $massUploadToProductProcessor->applyMassUpload(Argument::cetera())->shouldNotBeCalled();
        $massUploadToProductModelProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto('foobar', 'asset_collection'),
            ['car.png']
        )->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('Reason for success')->shouldBeCalled();

        $this->execute();
    }

    function it_skips_files_during_mass_upload(
        $stepExecution,
        $massUploadToProductProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new \SplFileInfo('file_b.jpg'),
            ProcessedItem::STATE_SKIPPED,
            'Reason to be skipped'
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_identifier')->willReturn('foobar');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');
        $jobParameters->get('imported_file_names')->willReturn(['car.png']);

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto('foobar', 'asset_collection'),
            ['car.png']
        )->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('variations_not_generated')->shouldBeCalled();
        $stepExecution->addWarning(
            'Reason to be skipped',
            [],
            new DataInvalidItem(['filename' => 'file_b.jpg'])
        )->shouldBeCalled();

        $this->execute();
    }

    function it_stops_the_mass_upload_in_case_of_errors_on_asset_generation(
        $stepExecution,
        $massUploadToProductProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
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
        $jobParameters->get('entity_identifier')->willReturn('foobar');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');
        $jobParameters->get('imported_file_names')->willReturn(['car.png']);

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto('foobar', 'asset_collection'),
            ['car.png']
        )->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('error')->shouldBeCalled();
        $stepExecution->addError('Exception message')->shouldBeCalled();

        $this->execute();
    }

    function it_stops_the_mass_upload_in_case_of_errors_on_entity_with_values_validation(
        $stepExecution,
        $massUploadToProductProcessor,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new EntityToAddAssetsInto('foobar', 'asset_collection'),
            ProcessedItem::STATE_ERROR,
            '',
            new \Exception('Exception message')
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('entity_type')->willReturn('product');
        $jobParameters->get('entity_identifier')->willReturn('foobar');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');
        $jobParameters->get('imported_file_names')->willReturn(['car.png']);

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto('foobar', 'asset_collection'),
            ['car.png']
        )->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('error')->shouldBeCalled();
        $stepExecution->addError('Exception message')->shouldBeCalled();

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
        $jobParameters->get('entity_identifier')->willReturn('foobar');
        $jobParameters->get('attribute_code')->willReturn('asset_collection');
        $jobParameters->get('imported_file_names')->willReturn(['car.png']);

        $massUploadToProductProcessor->applyMassUpload(
            new UploadContext('/tmp/pim/file_storage', 'username'),
            new EntityToAddAssetsInto('foobar', 'asset_collection'),
            ['car.png']
        )->willReturn($processedItemList);

        $this->shouldThrow(\InvalidArgumentException::class)->during('execute');
    }
}
