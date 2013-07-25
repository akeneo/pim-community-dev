<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Symfony\Component\Serializer\Serializer;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvSerializerProcessor extends SerializerProcessor
{
    protected $delimiter = ';';
    protected $enclosure = '"';
    protected $withHeader = false;

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
    }

    public function getEnclosure()
    {
        return $this->enclosure;
    }

    public function isWithHeader()
    {
        return $this->withHeader;
    }

    public function process($item)
    {
        return $this->serializer->serialize($item, 'csv', array(
            'delimiter'  => $this->delimiter,
            'enclosure'  => $this->enclosure,
            'withHeader' => $this->withHeader,
        ));
    }

    public function getConfigurationFields()
    {
        return array(
            'delimiter' => array(
                'type'    => 'text',
                'options' => array(),
            ),
            'enclosure' => array(
                'type'    => 'text',
                'options' => array(),
            ),
            'withHeader' => array(
                'type'    => 'checkbox',
                'options' => array(),
            ),

        );
    }
}

