<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\File;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
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
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Item\UploadedFileAwareInterface');
        $this->shouldImplement('\Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([
            'filePath' => [
                'options' => [
                    'label' => 'pim_base_connector.import.yamlFilePath.label',
                    'help'  => 'pim_base_connector.import.yamlFilePath.help'
                ]
            ],
            'uploadAllowed' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.import.uploadAllowed.label',
                    'help'  => 'pim_base_connector.import.uploadAllowed.help'
                ]
            ]
        ]);
    }

    function it_is_configurable(File $file)
    {
        $file->getRealPath()->willReturn('/path/to/file/img.jpg');

        $this->getFilePath()->shouldReturn(null);
        $this->isMultiple()->shouldReturn(false);
        $this->getCodeField()->shouldReturn('code');
        $this->isUploadAllowed()->shouldReturn(false);

        $this->setFilePath('/path/to/file/');
        $this->setMultiple(true);
        $this->setCodeField('custom_code');
        $this->setUploadAllowed(true);

        $this->getFilePath()->shouldReturn('/path/to/file/');
        $this->isMultiple()->shouldReturn(true);
        $this->getCodeField()->shouldReturn('custom_code');
        $this->isUploadAllowed()->shouldReturn(true);

        $this->setUploadedFile($file);

        $this->getFilePath()->shouldReturn('/path/to/file/img.jpg');
    }

    function it_provides_uploaded_file_constraints()
    {
        $constraints = $this->getUploadedFileConstraints();

        $constraints[0]->shouldBeAnInstanceOf('\Symfony\Component\Validator\Constraints\NotBlank');
        $constraints[1]->shouldBeAnInstanceOf('\Pim\Bundle\CatalogBundle\Validator\Constraints\File');
        $constraints[1]->allowedExtensions->shouldBe(['yml', 'yaml']);
    }

    function it_reads_entities_from_a_yml_file_one_by_one_incrementing_summary_info_for_each_one(
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(false, false);

        $stepExecution->incrementSummaryInfo('read_lines')->shouldBeCalledTimes(3);

        $this->setFilePath(
            realpath(__DIR__ . '/../../../../../../features/Context/fixtures/fake_products_with_code.yml')
        );
        $this->setStepExecution($stepExecution);
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
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(false, false);

        $stepExecution->incrementSummaryInfo(Argument::any())->shouldNotBeCalled();

        $this->setFilePath(
            realpath(__DIR__ . '/../../../../../../features/Context/fixtures/fake_products_with_code.yml')
        );
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
        StepExecution $stepExecution
    ) {
        $this->beConstructedWith(true, false);

        $stepExecution->incrementSummaryInfo('read_lines')->shouldBeCalled();

        $this->setFilePath(
            realpath(__DIR__ . '/../../../../../../features/Context/fixtures/fake_products_with_code.yml')
        );
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

    function it_reads_several_entities_without_code_from_a_yml_file()
    {
        $this->beConstructedWith(true, 'sku');

        $this->setFilePath(
            realpath(__DIR__ . '/../../../../../../features/Context/fixtures/fake_products_without_code.yml')
        );
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

    function it_initializes_the_class()
    {
        $this->beConstructedWith(false, false);

        $this->setFilePath(
            realpath(__DIR__ . '/../../../../../../features/Context/fixtures/fake_products_with_code.yml')
        );
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
