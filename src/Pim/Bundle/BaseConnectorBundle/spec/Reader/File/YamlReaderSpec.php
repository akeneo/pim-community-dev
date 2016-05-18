<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\File;

use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;

class YamlReaderSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Reader\File\YamlReader');
    }

    function it_is_an_item_reader_step_execution_and_uploaded_file_aware()
    {
        $this->shouldImplement('\Akeneo\Component\Batch\Item\ItemReaderInterface');
        $this->shouldImplement('\Akeneo\Component\Batch\Step\StepExecutionAwareInterface');
    }

    function it_reads_entities_from_a_yml_file_one_by_one_incrementing_summary_info_for_each_one(
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(false, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn(realpath(__DIR__ . '/../../fixtures/fake_products_with_code.yml'));

        $stepExecution->incrementSummaryInfo('read_lines')->shouldBeCalledTimes(3);

        $this->read()->shouldReturn([
            'sku' => 'mug_akeneo'
        ]);
        $this->read()->shouldReturn([
            'sku'   => 't_shirt_akeneo_purple',
            'color' => 'purple'
        ]);
        $this->read()->shouldReturn([
            'sku' => 'mouse_akeneo'
        ]);
    }

    function it_reads_entities_from_a_yml_file_one_by_one(
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(false, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn(realpath(__DIR__ . '/../../fixtures/fake_products_with_code.yml'));

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalled();

        $this->read()->shouldReturn([
            'sku' => 'mug_akeneo'
        ]);
        $this->read()->shouldReturn([
            'sku'   => 't_shirt_akeneo_purple',
            'color' => 'purple'
        ]);
        $this->read()->shouldReturn([
            'sku' => 'mouse_akeneo'
        ]);
    }

    function it_reads_several_entities_from_a_yml_file_incrementing_summary_info(
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(true, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn(realpath(__DIR__ . '/../../fixtures/fake_products_with_code.yml'));

        $stepExecution->incrementSummaryInfo('read_lines')->shouldBeCalled();

        $this->setStepExecution($stepExecution);
        $this->read()->shouldReturn([
            'mug_akeneo' => [
                'sku' => 'mug_akeneo'
            ],
            't_shirt_akeneo_purple' => [
                'sku'   => 't_shirt_akeneo_purple',
                'color' => 'purple'
            ],
            'mouse_akeneo' => [
                'sku' => 'mouse_akeneo'
            ]
        ]);
    }

    function it_reads_several_entities_without_code_from_a_yml_file(
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(true, 'sku');
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')
            ->willReturn(realpath(__DIR__ . '/../../fixtures/fake_products_without_code.yml'));

        $stepExecution->incrementSummaryInfo('read_lines')->shouldBeCalled();

        $this->read()->shouldReturn([
            'mug_akeneo_blue' => [
                'color' => 'blue',
                'sku'   => 'mug_akeneo_blue'
            ],
            't_shirt_akeneo_s_purple' => [
                'color' => 'purple',
                'size'  => 'S',
                'sku'   => 't_shirt_akeneo_s_purple'
            ],
            'mug_akeneo_purple' => [
                'color' => 'purple',
                'sku'   => 'mug_akeneo_purple'
            ]
        ]);
    }

    function it_initializes_the_class(
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith(false, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')
            ->willReturn(realpath(__DIR__ . '/../../fixtures/fake_products_with_code.yml'));

        $stepExecution->incrementSummaryInfo('read_lines')->shouldBeCalled();

        $this->read()->shouldReturn([
            'sku' => 'mug_akeneo'
        ]);

        $this->initialize();

        $this->read()->shouldReturn([
            'sku' => 'mug_akeneo'
        ]);
        $this->read()->shouldReturn([
            'sku'   => 't_shirt_akeneo_purple',
            'color' => 'purple'
        ]);
        $this->read()->shouldReturn([
            'sku' => 'mouse_akeneo'
        ]);
    }
}
