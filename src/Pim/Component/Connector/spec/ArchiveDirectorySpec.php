<?php

namespace spec\Pim\Component\Connector;

use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use League\Flysystem\Adapter\Local as LocalAdapter;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ArchiveDirectorySpec extends ObjectBehavior
{
    function let(Filesystem $filesystem, LocalAdapter $localAdapter)
    {
        $this->beConstructedWith($filesystem);

        $filesystem->getAdapter()->willReturn($localAdapter);
        $localAdapter->getPathPrefix()->willReturn('/path/prefix/app/archives/');
    }

    function it_retrieves_the_absolute_archiving_path_of_a_job_execution(
        $filesystem,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $filesystem->has(Argument::any())->willReturn(true);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getType()->willReturn('export');
        $jobInstance->getAlias()->willReturn('csv_family_export');
        $jobExecution->getId()->willReturn(14);

        $this->getAbsolute($jobExecution)->shouldReturn(
            '/path/prefix/app/archives/export/csv_family_export/14/output/'
        );
    }

    function it_creates_the_absolute_archiving_path_of_a_job_execution_if_it_does_not_exist(
        $filesystem,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $filesystem->has(Argument::any())->willReturn(false);
        $filesystem->createDir(Argument::any())->willReturn(true);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getType()->willReturn('export');
        $jobInstance->getAlias()->willReturn('csv_family_export');
        $jobExecution->getId()->willReturn(14);

        $this->getAbsolute($jobExecution)->shouldReturn(
            '/path/prefix/app/archives/export/csv_family_export/14/output/'
        );
    }

    function it_throws_an_exception_if_it_cannot_create_the_absolute_archiving_path_of_a_job_execution(
        $filesystem,
        JobExecution $jobExecution,
        JobInstance $jobInstance
    ) {
        $filesystem->has(Argument::any())->willReturn(false);
        $filesystem->createDir(Argument::any())->willReturn(false);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobInstance->getType()->willReturn('export');
        $jobInstance->getAlias()->willReturn('csv_family_export');
        $jobExecution->getId()->willReturn(14);

        $this->shouldThrow('\Exception')->during('getAbsolute', [$jobExecution]);
    }
}
