<?php

namespace spec\Pim\Component\Connector\Reader\File;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;

class CsvReaderSpec extends ObjectBehavior
{
    function let(FileIteratorInterface $fileIterator)
    {
        $this->beConstructedWith($fileIterator);
    }

    function it_is_configurable()
    {
        $this->setFilePath('/path/to/file/');
        $this->setDelimiter(';');
        $this->setEnclosure('-');
        $this->setEscape('\\');
        $this->setUploadAllowed(true);

        $this->getFilePath()->shouldReturn('/path/to/file/');
        $this->getDelimiter()->shouldReturn(';');
        $this->getEnclosure()->shouldReturn('-');
        $this->getEscape()->shouldReturn('\\');
        $this->isUploadAllowed()->shouldReturn(true);
    }

    function it_gives_configuration_field()
    {
        $this->getConfigurationFields()->shouldReturn([
            'filePath' => [
                'options' => [
                    'label' => 'pim_connector.import.filePath.label',
                    'help'  => 'pim_connector.import.filePath.help'
                ]
            ],
            'uploadAllowed' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_connector.import.uploadAllowed.label',
                    'help'  => 'pim_connector.import.uploadAllowed.help'
                ]
            ],
            'delimiter' => [
                'options' => [
                    'label' => 'pim_connector.import.delimiter.label',
                    'help'  => 'pim_connector.import.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_connector.import.enclosure.label',
                    'help'  => 'pim_connector.import.enclosure.help'
                ]
            ],
            'escape' => [
                'options' => [
                    'label' => 'pim_connector.import.escape.label',
                    'help'  => 'pim_connector.import.escape.help'
                ]
            ],
        ]);
    }

    function it_reads_csv_file($fileIterator)
    {
        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];

        $fileIterator->setReaderOptions(
            [
                'fieldDelimiter' => ';',
                'fieldEnclosure' => '"',
            ]
        )->willReturn($fileIterator);
        $fileIterator->reset()->shouldBeCalled();
        $fileIterator->isInitialized()->willReturn(false);
        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->current()->willReturn($data);

        $filePath = __DIR__ . '/../../../../../../features/Context/fixtures/with_media.csv';
        $this->setFilePath($filePath);
        $fileIterator->setFilePath($filePath)->willReturn($fileIterator);

        $this->read()->shouldReturn($data);
    }
}
