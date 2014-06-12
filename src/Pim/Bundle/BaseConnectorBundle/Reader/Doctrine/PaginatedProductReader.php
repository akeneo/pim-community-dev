<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\Doctrine;

use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Pim\Bundle\TransformBundle\Converter\MetricConverter;
use Pim\Bundle\BaseConnectorBundle\Validator\Constraints\Channel as ChannelConstraint;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Manager\CompletenessManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;

/**
 * Reads products one by one
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PaginatedProductReader extends AbstractConfigurableStepElement implements ItemReaderInterface
{
    /**
     * @var string
     *
     * @Assert\NotBlank(groups={"Execution"})
     * @ChannelConstraint
     */
    protected $channel;

    /** @var AbstractQuery */
    protected $query;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var boolean */
    private $executed = false;

    /** @var array */
    protected $results = array();

    /**
     * @param ProductRepositoryInterface $repository
     * @param ChannelManager             $channelManager
     * @param CompletenessManager        $completenessManager
     * @param MetricConverter            $metricConverter
     */
    public function __construct(
        ProductRepositoryInterface $repository,
        ChannelManager $channelManager,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter
    ) {
        $this->repository          = $repository;
        $this->channelManager      = $channelManager;
        $this->completenessManager = $completenessManager;
        $this->metricConverter     = $metricConverter;
    }

    /**
     * Set query used by the reader
     *
     * @param Doctrine\ORM\AbstractQuery $query
     *
     * @throws \InvalidArgumentException
     */
    public function setQuery($query)
    {
        if (!is_a($query, 'Doctrine\ORM\AbstractQuery', true)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '$query must be a Doctrine\ORM\AbstractQuery instance, got "%s"',
                    is_object($query) ? get_class($query) : $query
                )
            );
        }
        $this->query = $query;
    }

    /**
     * Get query to execute
     *
     * @return Doctrine\ORM\AbstractQuery
     *
     * @throws ORMReaderException
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
        if (!$this->executed) {
            $this->executed = true;
            if (!is_object($this->channel)) {
                $this->channel = $this->channelManager->getChannelByCode($this->channel);
            }
            $this->query = $this->repository
                ->buildByChannelAndCompleteness($this->channel)
                ->getQuery();

            $this->results = $this->getQuery()
                ->setFirstResult(0)
                ->setMaxResults(50000);
            $paginator = new Paginator($this->results, true);


            var_dump(count($paginator));
            foreach ($paginator as $product) {
                echo $product->getId() . "\n";
            }
            die();

        }
        $result = $this->results->current();

        if ($result) {
            $this->results->next();
            $this->stepExecution->incrementSummaryInfo('read');
        }



        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->executed = false;
        $this->query = false;
    }
    /**
     * Set channel
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;
    }

    /**
     * Get channel
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'channel' => array(
                'type'    => 'choice',
                'options' => array(
                    'choices'  => $this->channelManager->getChannelChoices(),
                    'required' => true,
                    'select2'  => true,
                    'label'    => 'pim_base_connector.export.channel.label',
                    'help'     => 'pim_base_connector.export.channel.help'
                )
            )
        );
    }
}
