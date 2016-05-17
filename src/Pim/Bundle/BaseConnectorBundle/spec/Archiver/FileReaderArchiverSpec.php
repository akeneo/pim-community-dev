<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Archiver;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Step\AbstractStep;
use Akeneo\Component\Batch\Step\ItemStep;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Bundle\BaseConnectorBundle\Reader\File\YamlReader;
use Pim\Bundle\BaseConnectorBundle\Reader\File\FileReader;
use Prophecy\Argument;
use Symfony\Component\DependencyInjection\ContainerInterface;

class FileReaderArchiverSpec extends ObjectBehavior
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
        $this->shouldHaveType('Pim\Bundle\BaseConnectorBundle\Archiver\FileReaderArchiver');
    }

    function it_create_a_file_when_reader_is_valid(
        $registry,
        YamlReader $reader,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        JobParameters $jobParameters,
        $filesystem
    ) {
        $pathname = tempnam(sys_get_temp_dir(), 'spec');
        $filename = basename($pathname);

        $registry->getJob($jobInstance)->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $jobExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn($pathname);

        $filesystem->put(
            'type' . DIRECTORY_SEPARATOR .
            'alias' . DIRECTORY_SEPARATOR .
            '12' . DIRECTORY_SEPARATOR .
            'input' . DIRECTORY_SEPARATOR .
            $filename,
            ''
        )->shouldBeCalled();

        $this->archive($jobExecution);

        unlink($pathname);
    }

    function it_doesnt_create_a_file_when_writer_is_invalid(
        $registry,
        ItemReaderInterface $reader,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        ItemStep $step,
        $filesystem
    ) {
        $registry->getJob($jobInstance)->willReturn($job);

        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);
        $step->getReader()->willReturn($reader);

        $filesystem->put(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_the_name_of_the_archiver()
    {
        $this->getName()->shouldReturn('input');
    }

    function it_doesnt_create_a_file_if_step_is_not_an_item_step(
        $registry,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job,
        AbstractStep $step,
        $filesystem
    ) {
        $registry->getJob($jobInstance)->willReturn($job);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $jobExecution->getId()->willReturn(12);
        $jobInstance->getType()->willReturn('type');
        $jobInstance->getAlias()->willReturn('alias');
        $job->getSteps()->willReturn([$step]);

        $filesystem->put(Argument::any())->shouldNotBeCalled();

        $this->archive($jobExecution);
    }

    function it_returns_true_for_the_supported_job(
        $registry,
        FileReader $reader,
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
        $step->getReader()->willReturn($reader);

        $this->supports($jobExecution)->shouldReturn(true);
    }

    function it_returns_false_for_the_unsupported_job(
        $registry,
        ItemReaderInterface $reader,
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
        $step->getReader()->willReturn($reader);

        $this->supports($jobExecution)->shouldReturn(false);
    }
}
