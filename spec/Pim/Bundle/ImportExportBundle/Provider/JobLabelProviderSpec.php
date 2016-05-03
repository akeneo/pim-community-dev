<?php

namespace spec\Pim\Bundle\ImportExportBundle\Provider;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Model\JobExecution;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Model\Warning;
use PhpSpec\ObjectBehavior;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class JobLabelProviderSpec extends ObjectBehavior
{
    function let(
        TranslatorInterface $translator,
        ContainerInterface $container,
        ConnectorRegistry $connectorRegistry
    ) {
        $container->get('akeneo_batch.connectors')->willReturn($connectorRegistry);

        $this->beConstructedWith($translator, $container);
    }

    function it_returns_a_job_label(
        $translator,
        $connectorRegistry,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job
    ) {
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $connectorRegistry->getJob($jobInstance)->willReturn($job);

        $job->getName()->willReturn('csv_product_import');
        $translator->trans('csv_product_import.label')->willReturn('CSV Product Import');

        $this->getJobLabel($jobExecution)->shouldReturn('CSV Product Import');
    }

    function it_returns_a_step_label(
        $translator,
        $connectorRegistry,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job
    ) {
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $connectorRegistry->getJob($jobInstance)->willReturn($job);

        $job->getName()->willReturn('csv_product_import');
        $stepExecution->getStepName()->willReturn('perform');
        $translator->trans('csv_product_import.perform.label')->willReturn('Import Products');

        $this->getStepLabel($stepExecution)->shouldReturn('Import Products');
    }

    function it_returns_a_step_warning_label(
        $translator,
        $connectorRegistry,
        Warning $stepWarning,
        StepExecution $stepExecution,
        JobExecution $jobExecution,
        JobInstance $jobInstance,
        Job $job
    ) {
        $stepWarning->getStepExecution()->willReturn($stepExecution);
        $stepExecution->getJobExecution()->willReturn($jobExecution);
        $jobExecution->getJobInstance()->willReturn($jobInstance);
        $connectorRegistry->getJob($jobInstance)->willReturn($job);

        $job->getName()->willReturn('csv_product_import');
        $stepExecution->getStepName()->willReturn('perform');
        $stepWarning->getName()->willReturn('duplicated');
        $translator->trans('csv_product_import.perform.warning.duplicated.label')->willReturn('Duplicated products');

        $this->getStepWarningLabel($stepWarning)->shouldReturn('Duplicated products');
    }
}
