<?php

namespace spec\Pim\Component\Connector\Transfer;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Pim\Component\Connector\ArchiveDirectory;
use Prophecy\Exception\Prediction\FailedPredictionException;
use Symfony\Component\Filesystem\Filesystem;

class LocalTransferStepElementSpec extends ObjectBehavior
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $specWorkingDir;

    function let(
        StepExecution $stepExecution,
        ArchiveDirectory $archiveDirectory,
        JobExecution $jobExecution,
        JobParameters $jobParameters
    ) {
        $this->specWorkingDir = sys_get_temp_dir() . '/akeneo/phpspec/';
        $this->filesystem = new Filesystem();
        $this->filesystem->mkdir($this->specWorkingDir);

        $this->beConstructedWith($archiveDirectory, true);

        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $this->setStepExecution($stepExecution);
    }

    function letGo()
    {
        $this->filesystem->remove($this->specWorkingDir);
    }

    function it_transfers_from_archive_dir_to_local_dir(
        $jobParameters,
        $stepExecution,
        ArchiveDirectory $archiveDirectory
    ) {
        $archiveFile = $this->specWorkingDir . 'archive/path/51/foo.csv';
        $this->filesystem->mkdir(dirname($archiveFile));
        $this->filesystem->touch($archiveFile);
        $archiveDirectory->getAbsolute(Argument::any())->willReturn(dirname($archiveFile));

        $exportFile = $this->specWorkingDir . 'transfer/local/path/foo.csv';
        $jobParameters->get('filePath')->willReturn($exportFile);

        $stepExecution->incrementSummaryInfo('transferred_files')->shouldBeCalled();

        $this->transfer();

        if (!is_file($exportFile)) {
            throw new FailedPredictionException(sprintf('The transfer has failed.'));
        }
    }
}
