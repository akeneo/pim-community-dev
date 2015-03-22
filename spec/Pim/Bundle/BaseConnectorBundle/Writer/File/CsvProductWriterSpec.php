<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use PhpSpec\ObjectBehavior;

class CsvProductWriterSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution)
    {
        $this->setStepExecution($stepExecution);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Writer\File\CsvProductWriter');
    }

    function it_is_an_item_writer()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface');
    }

    function it_is_step_execution_aware()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface');
    }

    function it_is_a_configurable_step_element()
    {
        $this->shouldHaveType('\Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement');
    }

    function it_is_an_archivable_writer()
    {
        $this->shouldHaveType('\Pim\Bundle\BaseConnectorBundle\Writer\File\ArchivableWriterInterface');
    }

    function it_provides_configuration_fields()
    {
        $this->getConfigurationFields()->shouldReturn([
            'filePath' => [
                'options' => [
                    'label' => 'pim_base_connector.export.filePath.label',
                    'help'  => 'pim_base_connector.export.filePath.help'
                ]
            ],
            'delimiter' => [
                'options' => [
                    'label' => 'pim_base_connector.export.delimiter.label',
                    'help'  => 'pim_base_connector.export.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_base_connector.export.enclosure.label',
                    'help'  => 'pim_base_connector.export.enclosure.help'
                ]
            ],
            'withHeader' => [
                'type' => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.export.withHeader.label',
                    'help'  => 'pim_base_connector.export.withHeader.help'
                ]
            ]
        ]);
    }

    function it_is_configurable()
    {
        $this->getDelimiter()->shouldReturn(';');
        $this->getEnclosure()->shouldReturn('"');
        $this->isWithHeader()->shouldReturn(true);

        $this->setDelimiter(',');
        $this->setEnclosure('^');
        $this->setWithHeader(false);

        $this->getDelimiter()->shouldReturn(',');
        $this->getEnclosure()->shouldReturn('^');
        $this->isWithHeader()->shouldReturn(false);
    }

    function it_writes_product_data_in_file_and_copy_medias($stepExecution)
    {
        $file = new \SplFileInfo(realpath(__DIR__.'/../../../../../../features/Context/fixtures/product_export_with_non_utf8_characters.csv'));
        $media = ['filePath' => $file->getPathname(), 'exportPath' => 'test.csv'];

        $this->write([['product' => 'my-product', 'media' => [$media]]]);
        $this->getWrittenFiles()->shouldReturn(['/tmp/test.csv' => 'test.csv']);
        $stepExecution->addWarning()->shouldNotBeCalled();
    }

    function it_does_not_copy_not_found_media($stepExecution)
    {
        $media = ['filePath' => 'not-found.csv', 'exportPath' => 'test.csv'];

        $this->write([['product' => 'my-product', 'media' => [$media]]]);
        $this->getWrittenFiles()->shouldReturn([]);
        $stepExecution->addWarning('csv_product_writer', 'The media has not been found or is not currently available', [], $media)
            ->shouldBeCalled();
    }

    function it_does_not_copy_with_wrong_directory($stepExecution)
    {
        $file = new \SplFileInfo(realpath(__DIR__.'/../../../../../../features/Context/fixtures/product_export_with_non_utf8_characters.csv'));
        $media = ['filePath' => $file->getPathname(), 'exportPath' => null];

        $previousReporting = error_reporting();
        error_reporting(0);

        $this->write([['product' => 'my-product', 'media' => [$media]]]);
        $this->getWrittenFiles()->shouldReturn([]);
        $stepExecution->addWarning('csv_product_writer', 'The media has not been copied', [], $media)
            ->shouldBeCalled();
        error_reporting($previousReporting);
    }
}
