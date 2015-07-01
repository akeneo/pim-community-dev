<?php

namespace spec\Pim\Bundle\BaseConnectorBundle\Reader\File;

use PhpSpec\ObjectBehavior;

class CsvReaderSpec extends ObjectBehavior
{
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
}
