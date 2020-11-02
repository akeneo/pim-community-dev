<?php

namespace spec\Akeneo\Tool\Component\Connector\Reader\File\Yaml;

use Akeneo\Tool\Component\Connector\Exception\InvalidItemFromViolationsException;
use Akeneo\Tool\Component\Batch\Job\JobParameters;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PhpSpec\ObjectBehavior;
use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\Exception\DataArrayConversionException;
use Prophecy\Argument;
use Symfony\Component\Validator\ConstraintViolationList;

class ReaderSpec extends ObjectBehavior
{
    function let(ArrayConverterInterface $converter)
    {
        $this->beConstructedWith($converter);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Akeneo\Tool\Component\Connector\Reader\File\Yaml\Reader');
    }

    function it_is_an_item_reader_step_execution_and_uploaded_file_aware()
    {
        $this->shouldImplement('\Akeneo\Tool\Component\Batch\Item\ItemReaderInterface');
        $this->shouldImplement('\Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface');
        $this->shouldImplement('\Akeneo\Tool\Component\Batch\Item\TrackableItemReaderInterface');
    }

    function it_return_empty_count_on_invalid_file(
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $incorrectlyFormattedFilePath = realpath(__DIR__ . '/../../../fixtures/fake_incorrectly_formatted_yml_file.yml');
        $jobParameters->get('filePath')->willReturn($incorrectlyFormattedFilePath);

        $stepExecution->setSummary(['item_position' => 0])->shouldBeCalledTimes(1);
        $this->totalItems()->shouldReturn(0);
    }

    function it_return_item_count(
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $incorrectlyFormattedFilePath = realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml');
        $jobParameters->get('filePath')->willReturn($incorrectlyFormattedFilePath);

        $stepExecution->setSummary(['item_position' => 0])->shouldBeCalledTimes(1);
        $this->totalItems()->shouldReturn(3);
    }

    function it_initializes_the_summary_info_if_the_yaml_file_is_not_correctly_formatted(
        ArrayConverterInterface $converter,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith($converter, false, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $incorrectlyFormattedFilePath = realpath(__DIR__ . '/../../../fixtures/fake_incorrectly_formatted_yml_file.yml');
        $jobParameters->get('filePath')->willReturn($incorrectlyFormattedFilePath);

        $stepExecution->setSummary(['item_position' => 0])->shouldBeCalledTimes(1);
        $this->read()->shouldReturn(null);
    }

    function it_reads_entities_from_a_yml_file_one_by_one_incrementing_summary_info_for_each_one(
        ArrayConverterInterface $converter,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith($converter, false, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn(realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml'));


        $stepExecution->setSummary(['item_position' => 0])->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalledTimes(3);

        $converter->convert(['sku' => 'mug_akeneo'])->willReturn(['sku' => 'mug_akeneo']);
        $converter->convert([
            'sku'   => 't_shirt_akeneo_purple',
            'color' => 'purple'
        ])->willReturn([
            'sku'   => 't_shirt_akeneo_purple',
            'color' => 'purple'
        ]);
        $converter->convert(['sku' => 'mouse_akeneo'])->willReturn(['sku' => 'mouse_akeneo']);

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

    function it_skips_an_item_in_case_of_conversion_error(
        ArrayConverterInterface $converter,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith($converter, false, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn(realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml'));

        $stepExecution->setSummary(['item_position' => 0])->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();
        $stepExecution->getSummaryInfo('item_position')->shouldBeCalled();

        $data = [
            'sku'  => 'mug_akeneo',
        ];

        $stepExecution->incrementSummaryInfo("skip")->shouldBeCalled();
        $converter->convert($data, Argument::any())->willThrow(
            new DataArrayConversionException('message', 0, null, new ConstraintViolationList())
        );

        $this->shouldThrow(InvalidItemFromViolationsException::class)->during('read');
    }

    function it_reads_entities_from_a_yml_file_one_by_one(
        ArrayConverterInterface $converter,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith($converter, false, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn(realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml'));

        $stepExecution->setSummary(['item_position' => 0])->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo(Argument::any())->shouldBeCalled();

        $converter->convert(['sku' => 'mug_akeneo'])->willReturn(['sku' => 'mug_akeneo']);
        $converter->convert([
            'sku'   => 't_shirt_akeneo_purple',
            'color' => 'purple'
        ])->willReturn([
            'sku'   => 't_shirt_akeneo_purple',
            'color' => 'purple'
        ]);
        $converter->convert(['sku' => 'mouse_akeneo'])->willReturn(['sku' => 'mouse_akeneo']);

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
        ArrayConverterInterface $converter,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith($converter, true, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')->willReturn(realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml'));

        $stepExecution->setSummary(['item_position' => 0])->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $result = [
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
        ];

        $converter->convert($result)->willReturn($result);
        $this->setStepExecution($stepExecution);
        $this->read()->shouldReturn($result);
    }

    function it_reads_several_entities_without_code_from_a_yml_file(
        ArrayConverterInterface $converter,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith($converter, true, 'sku');
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')
            ->willReturn(realpath(__DIR__ . '/../../../fixtures/fake_products_without_code.yml'));

        $stepExecution->setSummary(['item_position' => 0])->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $result = [
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
        ];

        $converter->convert($result)->willReturn($result);
        $this->read()->shouldReturn($result);
    }

    function it_initializes_the_class(
        ArrayConverterInterface $converter,
        StepExecution $stepExecution,
        JobParameters $jobParameters
    ) {
        $this->beConstructedWith($converter, false, false);
        $this->setStepExecution($stepExecution);
        $stepExecution->getJobParameters()->willReturn($jobParameters);
        $jobParameters->get('filePath')
            ->willReturn(realpath(__DIR__ . '/../../../fixtures/fake_products_with_code.yml'));

        $stepExecution->setSummary(['item_position' => 0])->shouldBeCalledTimes(1);
        $stepExecution->incrementSummaryInfo('item_position')->shouldBeCalled();

        $converter->convert(['sku' => 'mug_akeneo'])->willReturn(['sku' => 'mug_akeneo']);
        $converter->convert([
            'sku'   => 't_shirt_akeneo_purple',
            'color' => 'purple'
        ])->willReturn([
            'sku'   => 't_shirt_akeneo_purple',
            'color' => 'purple'
        ]);
        $converter->convert(['sku' => 'mouse_akeneo'])->willReturn(['sku' => 'mouse_akeneo']);

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
