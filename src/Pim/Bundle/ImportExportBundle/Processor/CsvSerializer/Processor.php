<?php

namespace Pim\Bundle\ImportExportBundle\Processor\CsvSerializer;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\SerializerInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Item\ItemProcessorInterface;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Oro\Bundle\BatchBundle\Entity\StepExecution;

/**
 * An abstract processor to serialize data into csv
 *
 * Use either one of the following services given the type of data to serialize
 *   - HeterogeneousProcessor (id: pim_import_export.processor.csv_serializer.heterogeneous)
 *   - HomogeneousProcessor   (id: pim_import_export.processor.csv_serializer.homogeneous)
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @abstract
 */
abstract class Processor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={",", ";", "|"}, message="The value must be one of , or ; or |")
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"}, message="The value must be one of "" or '")
     * @var string
     */
    protected $enclosure = '"';

    /**
     * @var boolean
     */
    protected $withHeader = true;

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    /**
     * Constructor
     *
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
            'delimiter' => array(
                'options' => array(
                    'label' => 'pim_import_export.export.delimiter.label',
                    'help'  => 'pim_import_export.export.delimiter.help'
                )
            ),
            'enclosure' => array(
                'options' => array(
                    'label' => 'pim_import_export.export.enclosure.label',
                    'help'  => 'pim_import_export.export.enclosure.help'
                )
            ),
            'withHeader' => array(
                'type' => 'switch',
                'options' => array(
                    'label' => 'pim_import_export.export.withHeader.label',
                    'help'  => 'pim_import_export.export.withHeader.help'
                )
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
