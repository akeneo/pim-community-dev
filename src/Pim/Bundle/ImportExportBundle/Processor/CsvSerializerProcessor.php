<?php

namespace Pim\Bundle\ImportExportBundle\Processor;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\SerializerInterface;
use Pim\Bundle\ImportExportBundle\AbstractConfigurableStepElement;
use Pim\Bundle\BatchBundle\Item\ItemProcessorInterface;

/**
 * An abstract processor to serialize data into csv
 *
 * Use either one of the following services given the type of data to serialize
 *   - HeterogeneousCsvSerializerProcessor (id: pim_import_export.processor.heterogeneous_csv_serializer)
 *   - HomogeneousCsvSerializerProcessor   (id: pim_import_export.processor.homogeneous_csv_serializer)
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class CsvSerializerProcessor extends AbstractConfigurableStepElement implements ItemProcessorInterface
{
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={",", ";", "|"}, message="The value must be one of , or ; or |")
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"}, message="The value must be one of "" or '")
     */
    protected $enclosure = '"';

    protected $withHeader = true;

    /**
     * @param SerializerInterface $serializer
     */
    public function __construct(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Set the csv delimiter character
     *
     * @param string $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    /**
     * Get the csv delimiter character
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->delimiter;
    }

    /**
     * Set the csv enclosure character
     *
     * @param string $enclosure
     */
    public function setEnclosure($enclosure)
    {
        $this->enclosure = $enclosure;
    }

    /**
     * Get the csv enclosure character
     *
     * @return string
     */
    public function getEnclosure()
    {
        return $this->enclosure;
    }

    /**
     * Set wether or not to print a header row into the csv
     *
     * @param boolean $withHeader
     */
    public function setWithHeader($withHeader)
    {
        $this->withHeader = $withHeader;
    }

    /**
     * Get wether or not to print a header row into the csv
     *
     * @return boolean
     */
    public function isWithHeader()
    {
        return $this->withHeader;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'delimiter' => array(),
            'enclosure' => array(),
            'withHeader' => array(
                'type' => 'checkbox',
            ),
        );
    }
}
