<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\Doctrine;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\StepExecution;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

/**
 * Reads products for Mongodb
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated Will be removed in 1.7, please use Pim\Component\Connector\Reader\ProductReader instead.
 */
class ODMProductReader extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var AbstractQuery */
    protected $query;

    /** @var Cursor */
    protected $products;

    /** @var DocumentManager */
    protected $documentManager;

    /** @var ProductRepositoryInterface */
    protected $repository;

    /** @var CompletenessManager */
    protected $completenessManager;

    /** @var MetricConverter */
    protected $metricConverter;

    /** @var bool */
    protected $executed = false;

    /** @var bool */
    protected $missingCompleteness;

    /**
     * @param ProductRepositoryInterface $repository
     * @param ChannelRepositoryInterface $channelRepository
     * @param CompletenessManager        $completenessManager
     * @param MetricConverter            $metricConverter
     * @param DocumentManager            $documentManager
     * @param bool                       $missingCompleteness
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        DocumentManager $documentManager,
        $missingCompleteness = true
    ) {
        $this->documentManager     = $documentManager;
        $this->repository          = $repository;
        $this->channelRepository   = $channelRepository;
        $this->completenessManager = $completenessManager;
        $this->metricConverter     = $metricConverter;
        $this->missingCompleteness = $missingCompleteness;
    }

    /**
     * Get query to execute
     *
     * @return AbstractQuery
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $this->documentManager->clear();

        if (!$this->executed) {
            $this->executed = true;
            $jobParameters = $this->stepExecution->getJobParameters();
            $channelCode = $jobParameters->get('channel');
            $channel = $this->channelRepository->findOneByIdentifier($channelCode);

            if ($this->missingCompleteness) {
                $this->completenessManager->generateMissingForChannel($channel);
            }

            $this->query = $this->repository
                ->buildByChannelAndCompleteness($channel)
                ->getQuery();

            $this->products = $this->getQuery()->execute();

            // MongoDB Cursor are not positioned on first element (whereas ArrayIterator is)
            // as long as getNext() hasn't be called
            $this->products->getNext();
        }

        $result = $this->products->current();

        if ($result) {
            $this->metricConverter->convert($result, $channel);
            $this->stepExecution->incrementSummaryInfo('read');
            $this->products->next();
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->query = null;
        $this->documentManager->clear();
        $this->executed = false;
    }
}
