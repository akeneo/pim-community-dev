<?php

namespace spec\Pim\Component\Connector\Reader\File;

use Box\Spout\Reader\ReaderInterface;
use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;

class XlsxReaderSpec extends ObjectBehavior
{
    function let(FileIteratorInterface $fileIterator)
    {
        $this->beConstructedWith($fileIterator);
    }

    function it_is_configurable()
    {
        $this->setFilePath('/path/to/file/');
        $this->setUploadAllowed(true);

        $this->getFilePath()->shouldReturn('/path/to/file/');
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
        ]);
    }

    function it_read_csv_file($fileIterator, ReaderInterface $reader)
    {
        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];

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
