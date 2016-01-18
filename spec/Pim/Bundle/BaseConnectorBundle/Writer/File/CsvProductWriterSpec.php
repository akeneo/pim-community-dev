<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use League\Flysystem\Filesystem;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Connector\Writer\File\FileExporterInterface;

class CsvProductWriterSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution, FileExporterInterface $fileExporter)
    {
        $this->beConstructedWith($fileExporter);
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

    function it_writes_product_data_in_file_and_copies_medias($fileExporter, $stepExecution)
    {
        $media = ['filePath' => '1/2/3/4/1234-the-file.csv', 'exportPath' => 'test.csv', 'storageAlias' => FileStorage::CATALOG_STORAGE_ALIAS];
        $fileExporter->export('1/2/3/4/1234-the-file.csv', '/tmp/test.csv', FileStorage::CATALOG_STORAGE_ALIAS)->shouldBeCalled();

        $this->getPath();
        $this->write(
            [
                [
                    'product' => [
                        'sku'  => '001',
                        'name' => 'Michel'
                    ],
                    'media'   => [$media]
                ]
            ]
        );
        $this->getWrittenFiles()->shouldReturn(['/tmp/test.csv' => 'test.csv']);
        $stepExecution->addWarning()->shouldNotBeCalled();
    }

    function it_does_not_copy_medias_that_are_not_present_on_the_filesystem($fileExporter, $stepExecution)
    {
        $media = ['filePath' => 'not-found.jpg', 'exportPath' => 'test.jpg', 'storageAlias' => FileStorage::CATALOG_STORAGE_ALIAS];
        $fileExporter->export('not-found.jpg', '/tmp/test.jpg', FileStorage::CATALOG_STORAGE_ALIAS)->willThrow(new FileTransferException());

        $this->write(
            [
                [
                    'product' => [
                        'sku'  => '002',
                        'name' => 'Mireille'
                    ],
                    'media'   => [$media]
                ]
            ]
        );
        $this->getWrittenFiles()->shouldReturn([]);
        $stepExecution->addWarning('csv_product_writer', 'The media has not been found or is not currently available', [], $media)
            ->shouldBeCalled();
    }

    function it_does_not_copy_medias_that_are_not_downloadable($fileExporter, $stepExecution, Filesystem $fs)
    {
        $media = ['filePath' => 'copy-error.jpg', 'exportPath' => 'test.jpg', 'storageAlias' => FileStorage::CATALOG_STORAGE_ALIAS];
        $fileExporter->export('copy-error.jpg', '/tmp/test.jpg', FileStorage::CATALOG_STORAGE_ALIAS)->willThrow(new \LogicException('Copy error.'));

        $this->write(
            [
                [
                    'product' => [
                        'sku'  => '003',
                        'name' => 'Monique'
                    ],
                    'media'   => [$media]
                ]
            ]
        );
        $this->getWrittenFiles()->shouldReturn([]);
        $stepExecution->addWarning('csv_product_writer', 'The media has not been copied. Copy error.', [], $media)
            ->shouldBeCalled();
    }
}
