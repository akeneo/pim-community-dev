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
        return $this->serializer->serialize(
            $item,
            'csv',
            array(
                'delimiter'  => $this->delimiter,
                'enclosure'  => $this->enclosure,
                'withHeader' => $this->withHeader,
            )
        );
    }

    public function getName()
    {
        return 'CSV Serializer';
    }

    public function getConfigurationFields()
    {
        return array(
            'delimiter' => array(
                'type' => 'text',
            ),
            'enclosure' => array(
                'type' => 'text',
            ),
            'withHeader' => array(
                'type' => 'checkbox',
            ),

        );
    }
}
