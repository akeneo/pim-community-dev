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

namespace Specification\Akeneo\Asset\Bundle\MassUpload;

use Akeneo\Asset\Bundle\MassUpload\MassUploadTasklet;
use Akeneo\Asset\Component\ProcessedItem;
use Akeneo\Asset\Component\ProcessedItemList;
use Akeneo\Asset\Component\Upload\UploadContext;
use Akeneo\Tool\Component\Batch\Item\DataInvalidItem;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use Akeneo\Asset\Component\Upload\MassUpload\MassUploadProcessor;
use Prophecy\Argument;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
class MassUploadTaskletSpec extends ObjectBehavior
{
    function let(MassUploadProcessor $processor, StepExecution $stepExecution)
    {
        $this->beConstructedWith($processor);
        $this->setStepExecution($stepExecution);
    }

    function it_is_a_mass_upload_tasklet()
    {
        $this->shouldHaveType(MassUploadTasklet::class);
    }

    function it_is_a_tasklet()
    {
        $this->shouldImplement(TaskletInterface::class);
    }

    function it_mass_upload_files(
        $stepExecution,
        $processor,
        JobExecution $jobExecution
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new \SplFileInfo('file_a.jpg'),
            ProcessedItem::STATE_SUCCESS,
            'Reason for success'
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');
        $processor->applyMassUpload(new UploadContext(sys_get_temp_dir(), 'username'))->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('Reason for success')->shouldBeCalled();

        $this->execute();
    }

    function it_skips_files_during_mass_upload(
        $stepExecution,
        $processor,
        JobExecution $jobExecution
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(
            new \SplFileInfo('file_b.jpg'),
            ProcessedItem::STATE_SKIPPED,
            'Reason to be skipped'
        );

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');
        $processor->applyMassUpload(new UploadContext(sys_get_temp_dir(), 'username'))->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('variations_not_generated')->shouldBeCalled();
        $stepExecution->addWarning(
            'Reason to be skipped',
            [],
            new DataInvalidItem(['filename' => 'file_b.jpg'])
        )->shouldBeCalled();

        $this->execute();
    }

    function it_stops_the_mass_upload_in_case_of_errors(
        $stepExecution,
        $processor,
        JobExecution $jobExecution
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
        $processor->applyMassUpload(new UploadContext(sys_get_temp_dir(), 'username'))->willReturn($processedItemList);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('error')->shouldBeCalled();
        $stepExecution->addError('Exception message')->shouldBeCalled();

        $this->execute();
    }

    function it_throws_an_exception_if_processed_item_is_not_a_file(
        $stepExecution,
        $processor,
        JobExecution $jobExecution
    ) {
        $processedItemList = new ProcessedItemList();
        $processedItemList->addItem(new \StdClass(), ProcessedItem::STATE_SUCCESS, 'Reason for success');

        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getUser()->willReturn('username');
        $processor->applyMassUpload(new UploadContext(sys_get_temp_dir(), 'username'))->willReturn($processedItemList);

        $this->shouldThrow(\InvalidArgumentException::class)->during('execute');
    }
}
