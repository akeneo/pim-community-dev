<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CsvSerializerProcessor extends SerializerProcessor
{
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={",", ";", "|"})
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"})
     */
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

    public function setWithHeader($withHeader)
    {
        $this->withHeader = $withHeader;
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

    public function getName()
    {
        return 'CSV Serializer';
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
