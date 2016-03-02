<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An abstract processor to serialize data into csv
 *
 * Use either one of the following services given the type of data to serialize
 *   - HeterogeneousProcessor (id: pim_base_connector.processor.csv_serializer.heterogeneous)
 *   - HomogeneousProcessor   (id: pim_base_connector.processor.csv_serializer.homogeneous)
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class Processor extends AbstractConfigurableStepElement implements
    ItemProcessorInterface,
    StepExecutionAwareInterface
{
    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={",", ";", "|"}, message="The value must be one of , or ; or |")
     *
     * @var string
     */
    protected $delimiter = ';';

    /**
     * @Assert\NotBlank
     * @Assert\Choice(choices={"""", "'"}, message="The value must be one of "" or '")
     *
     * @var string
     */
    protected $enclosure = '"';

    /** @var bool */
    protected $withHeader = true;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var SerializerInterface */
    protected $serializer;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * Constructor
     *
     * @param SerializerInterface       $serializer
     * @param LocaleRepositoryInterface $localeRepository
     */
    public function __construct(SerializerInterface $serializer, LocaleRepositoryInterface $localeRepository)
    {
        $this->serializer       = $serializer;
        $this->localeRepository = $localeRepository;
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
     * Set whether or not to print a header row into the csv
     *
     * @param bool $withHeader
     */
    public function setWithHeader($withHeader)
    {
        $this->withHeader = $withHeader;
    }

    /**
     * Get whether or not to print a header row into the csv
     *
     * @return bool
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
        return [
            'delimiter' => [
                'options' => [
                    'label' => 'pim_base_connector.export.delimiter.label',
                    'help'  => 'pim_base_connector.export.delimiter.help'
                ]
            ],
            'enclosure' => [
                'options' => [
                    'label' => 'pim_base_connector.export.enclosure.label',
                    'help'  => 'pim_base_connector.export.enclosure.help'
                ]
            ],
            'withHeader' => [
                'type'    => 'switch',
                'options' => [
                    'label' => 'pim_base_connector.export.withHeader.label',
                    'help'  => 'pim_base_connector.export.withHeader.help'
                ]
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
