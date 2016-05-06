<?php

namespace spec\Pim\Component\Connector\Reader\File;

use PhpSpec\ObjectBehavior;
use Pim\Component\Connector\Reader\File\FileIteratorFactory;
use Pim\Component\Connector\Reader\File\FileIteratorInterface;

class XlsxReaderSpec extends ObjectBehavior
{
    function let(FileIteratorFactory $fileIteratorFactory)
    {
        $this->beConstructedWith($fileIteratorFactory);
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

    function it_read_csv_file($fileIteratorFactory, FileIteratorInterface $fileIterator)
    {
        $data = [
            'sku'  => 'SKU-001',
            'name' => 'door',
        ];

        $filePath = __DIR__ . DIRECTORY_SEPARATOR . '..' .
                    DIRECTORY_SEPARATOR . '..' .
                    DIRECTORY_SEPARATOR . '..' .
                    DIRECTORY_SEPARATOR . '..' .
                    DIRECTORY_SEPARATOR . '..' .
                    DIRECTORY_SEPARATOR . '..' .
                    DIRECTORY_SEPARATOR . 'features' .
                    DIRECTORY_SEPARATOR . 'Context' .
                    DIRECTORY_SEPARATOR . 'fixtures' .
                    DIRECTORY_SEPARATOR . 'product_with_carriage_return.xlsx';
        $this->setFilePath($filePath);

        $fileIteratorFactory->create($filePath)->willReturn($fileIterator);

        $fileIterator->rewind()->shouldBeCalled();
        $fileIterator->next()->shouldBeCalled();
        $fileIterator->valid()->willReturn(true);
        $fileIterator->current()->willReturn($data);

        $this->read()->shouldReturn($data);
    }
}
