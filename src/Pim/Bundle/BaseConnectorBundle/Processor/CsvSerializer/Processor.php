<?php

namespace Pim\Bundle\BaseConnectorBundle\Processor\CsvSerializer;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Symfony\Component\Serializer\SerializerInterface;

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
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
