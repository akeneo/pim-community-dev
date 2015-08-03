<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Writer\File;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\FileStorage\Exception\FileTransferException;
use Akeneo\Component\FileStorage\RawFile\RawFileFetcherInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;
use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\FileStorage;

class CsvProductWriterSpec extends ObjectBehavior
{
    function let(StepExecution $stepExecution, MountManager $mountManager, RawFileFetcherInterface $fileFetcher)
    {
        $this->beConstructedWith($mountManager, $fileFetcher);
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

    function it_writes_product_data_in_file_and_copies_medias($mountManager, $fileFetcher, $stepExecution, Filesystem $fs)
    {
        $file = new \SplFileInfo(realpath(__DIR__.'/../../../../../../features/Context/fixtures/product_export_with_non_utf8_characters.csv'));

        $media = ['filePath' => '1/2/3/4/1234-the-file.csv', 'exportPath' => 'test.csv', 'storageAlias' => FileStorage::CATALOG_STORAGE_ALIAS];
        $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS)->willReturn($fs);
        $fileFetcher->fetch('1/2/3/4/1234-the-file.csv', $fs)->willReturn($file);

        $this->write([['product' => 'my-product', 'media' => [$media]]]);
        $this->getWrittenFiles()->shouldReturn(['/tmp/test.csv' => 'test.csv']);
        $stepExecution->addWarning()->shouldNotBeCalled();
    }

    function it_does_not_copy_medias_that_are_not_present_on_the_filesystem($mountManager, $fileFetcher, $stepExecution, Filesystem $fs)
    {
        $media = ['filePath' => 'not-found.csv', 'exportPath' => 'test.csv', 'storageAlias' => FileStorage::CATALOG_STORAGE_ALIAS];
        $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS)->willReturn($fs);
        $fileFetcher->fetch('not-found.csv', $fs)->willThrow(new \LogicException());

        $this->write([['product' => 'my-product', 'media' => [$media]]]);
        $this->getWrittenFiles()->shouldReturn([]);
        $stepExecution->addWarning('csv_product_writer', 'The media has not been found on the file storage', [], $media)
            ->shouldBeCalled();
    }

    function it_does_not_copy_medias_that_are_not_downloadable($mountManager, $fileFetcher, $stepExecution, Filesystem $fs)
    {
        $media = ['filePath' => 'not-downlaodable.csv', 'exportPath' => 'test.csv', 'storageAlias' => FileStorage::CATALOG_STORAGE_ALIAS];
        $mountManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS)->willReturn($fs);
        $fileFetcher->fetch('not-downlaodable.csv', $fs)->willThrow(new FileTransferException());

        $this->write([['product' => 'my-product', 'media' => [$media]]]);
        $this->getWrittenFiles()->shouldReturn([]);
        $stepExecution->addWarning('csv_product_writer', 'Impossible to copy the media from the file storage', [], $media)
            ->shouldBeCalled();
    }
}
