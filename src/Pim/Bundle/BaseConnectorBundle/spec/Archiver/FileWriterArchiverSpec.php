<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Step\AbstractStep;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Writer\File\CsvWriter;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FileWriterArchiverSpec extends ObjectBehavior
{
    function let(
        Filesystem $filesystem,
        ContainerInterface $container,
        ConnectorRegistry $registry
    ) {
        $this->beConstructedWith($filesystem, $container);
        $container->get('akeneo_batch.connectors')->willReturn($registry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Archiver\FileWriterArchiver');
    }

    function it_creates_a_file_when_writer_is_valid(
        $filesystem,
        $registry,
        CsvWriter $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $registry->getJob($jobInstance)->willReturn($job);

        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $filename = basename($pathname);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');

        $job->getSteps()->willReturn([$step]);

        $step->getWriter()->willReturn($writer);

        $writer->getWrittenFiles()->willReturn([$pathname => $filename]);
        $writer->getPath()->willReturn($pathname);

        $filesystem->put(
            'type' . DIRECTORY_SEPARATOR .
            'alias' . DIRECTORY_SEPARATOR .
            '12' . DIRECTORY_SEPARATOR .
            'output' . DIRECTORY_SEPARATOR .
            $filename,
            ''
        )->shouldBeCalled();

        $this->archive($jobExecution);

        unlink($pathname);
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        $filesystem,
        $registry,
        ItemWriterInterface $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $registry->getJob($jobInstance)->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);

        $filesystem->put(Argument::cetera())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_the_name_of_the_archiver()
    {
        $this->getName()->shouldReturn('output');
    }

    function it_doesnt_create_a_file_if_step_is_not_an_item_step(
        $registry,
        $filesystem,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        AbstractStep $step
    ) {
        $registry->getJob($jobInstance)->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);

        $filesystem->put(Argument::cetera())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_supports_a_compatible_job(
        $registry,
        CsvWriter $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $registry->getJob($jobInstance)->willReturn($job);
        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $filename = basename($pathname);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);
        $writer->getWrittenFiles()->willReturn([$pathname => $filename]);
        $writer->getPath()->willReturn($pathname);

        $this->supports($jobExecution)->shouldReturn(true);

        unlink($pathname);
    }

    function it_does_not_support_a_incompatible_job(
        $registry,
        ItemWriterInterface $writer,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step
    ) {
        $registry->getJob($jobInstance)->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getWriter()->willReturn($writer);

        $this->supports($jobExecution)->shouldReturn(false);
    }
}
