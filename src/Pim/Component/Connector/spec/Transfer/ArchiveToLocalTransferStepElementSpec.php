<?php

namespace spec\Pim\Component\Connector\Transfer;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Component\Connector\ArchiveStorage;
use Prophecy\Exception\Prediction\FailedPredictionException;
use Symfony\Component\Filesystem\Filesystem;

class ArchiveToLocalTransferStepElementSpec extends ObjectBehavior
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $specWorkingDir;

    function let(
        StepExecution $stepExecution,
        ArchiveStorage $archiveStorage,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $this->specWorkingDir = sys_get_temp_dir() . '/akeneo/phpspec/';
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->specWorkingDir);

        $this->beConstructedWith($archiveStorage);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $this->setStepExecution($stepExecution);
    }

    function letGo()
    {
        $this->filesystem->remove($this->specWorkingDir);
    }

    function it_transfers_a_single_file_from_archive_dir_to_local_dir(
        $jobParameters,
        $stepExecution,
        $jobExecution,
        ArchiveStorage $archiveStorage,
        JobInstance $jobInstance
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getCode()->willReturn('csv_family_export');

        $archivePathname = $this->createArchiveFile('csv_family_export');
        $archiveStorage->getAbsoluteDirectory(Argument::any())->willReturn(dirname($archivePathname));

        $jobParameters->get('filePath')->willReturn($this->specWorkingDir . 'transfer/local/path/family.csv');
        $stepExecution->incrementSummaryInfo('transferred_files')->shouldBeCalled();

        $this->transfer();

        if (!is_file($this->specWorkingDir . 'transfer/local/path/family.csv')) {
            throw new FailedPredictionException(sprintf('The transfer has failed.'));
        }
    }

    function it_transfers_several_files_from_archive_dir_to_local_dir(
        $jobParameters,
        $stepExecution,
        $jobExecution,
        ArchiveStorage $archiveStorage,
        JobInstance $jobInstance
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getCode()->willReturn('xlsx_family_export');

        $archivePathname1 = $this->createArchiveFile('xlsx_family_export_1');
        $archiveStorage->getAbsoluteDirectory(Argument::any())->willReturn(dirname($archivePathname1));
        $archivePathname2 = $this->createArchiveFile('xlsx_family_export_2');
        $archiveStorage->getAbsoluteDirectory(Argument::any())->willReturn(dirname($archivePathname2));

        $jobParameters->get('filePath')->willReturn($this->specWorkingDir . 'transfer/local/path/family.xlsx');
        $stepExecution->incrementSummaryInfo('transferred_files')->shouldBeCalled();

        $this->transfer();

        if (!is_file($this->specWorkingDir . 'transfer/local/path/family_1.xlsx') ||
            !is_file($this->specWorkingDir . 'transfer/local/path/family_2.xlsx')
        ) {
            throw new FailedPredictionException(sprintf('The transfer has failed.'));
        }
    }

    /**
     * @param string $filename
     *
     * @return string
     */
    private function createArchiveFile($filename)
    {
        $archivePathname = $this->specWorkingDir . 'archive/path/51/' . $filename;
        $this->filesystem->mkdir(dirname($archivePathname));
        $this->filesystem->touch($archivePathname);

        return $archivePathname;
    }
}
