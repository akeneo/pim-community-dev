<?php

namespace spec\Pim\Component\Connector\Reader\File;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;
use Prophecy\Argument;

class CsvReaderSpec extends ObjectBehavior
{
    function let(FileIteratorFactory $fileIteratorFactory)
    {
        $this->beConstructedWith($fileIteratorFactory);
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

    function it_reads_csv_file($fileIteratorFactory, FileIteratorInterface $fileIterator)
    {
        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];

        $filePath = $this->getPath() . DIRECTORY_SEPARATOR  . 'with_media.csv';
        $this->setFilePath($filePath);

        $fileIteratorFactory->create($filePath, [
            'fieldDelimiter' => ';',
            'fieldEnclosure' => '"',
        ])->willReturn($fileIterator);

        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
        $fileIterator->current()->willReturn($data);

        $this->read()->shouldReturn($data);
    }

    private function getPath()
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '..' .
               DIRECTORY_SEPARATOR  . '..' .
               DIRECTORY_SEPARATOR  . '..'.
               DIRECTORY_SEPARATOR  . '..'.
               DIRECTORY_SEPARATOR  . '..'.
               DIRECTORY_SEPARATOR  . '..' .
               DIRECTORY_SEPARATOR  . 'features' .
               DIRECTORY_SEPARATOR  . 'Context' .
               DIRECTORY_SEPARATOR  . 'fixtures';
    }
}
