<?php

namespace Pim\Bundle\EnrichBundle\Reader\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Job\DoctrineJobRepository;
use Doctrine\ORM\EntityManager;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepository;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product reader for mass edit, using the cursor and the pim filters
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterProductReader extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var array */
    protected $configuration = [];

    /** @var StepExecution */
    protected $stepExecution;

    /** @var EntityManager */
    protected $entityManager;

    /** @var \ArrayIterator */
    protected $products;

    /** @var Boolean */
    protected $executed = false;

    /** @var string */
    protected $channel;

    /** @var DoctrineJobRepository */
    protected $jobRepository;

    /** @var string */
    protected $massEditType;

    /** @var MassEditRepository */
    protected $massEditRepository;

    /**
     * Constructor
     *
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param EntityManager                       $entityManager
     * @param DoctrineJobRepository               $jobRepository
     * @param MassEditRepository                  $massEditRepository
     * @param string                              $massEditType
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        EntityManager $entityManager,
        DoctrineJobRepository $jobRepository,
        MassEditRepository $massEditRepository,
        $massEditType
    ) {
        $this->pqbFactory         = $pqbFactory;
        $this->entityManager      = $entityManager;
        $this->jobRepository      = $jobRepository;
        $this->massEditRepository = $massEditRepository;
        $this->massEditType       = $massEditType;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $configuration = $this->getJobConfiguration();

        if (!$this->executed) {
            $this->executed = true;
            $this->products = $this->getProductsCursor($configuration['filters']);
            $this->products->next();
        }

        $result = $this->products->current();

        if ($result) {
            $result = ['product' => $result, 'actions' => $configuration['actions']];
            $this->stepExecution->incrementSummaryInfo('read');
            $this->products->next();
        } else {
            $result = null;
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
        $this->entityManager->clear();
        $this->executed = false;
    }

    /**
     * @param array $filters
     *
     * @return \Akeneo\Component\StorageUtils\Cursor\CursorInterface
     */
    protected function getProductsCursor(array $filters)
    {
        $productQueryBuilder = $this->getProductQueryBuilder();

        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value']);
        $resolver->setOptional(['locale', 'scope']);
        $resolver->setDefaults(['locale' => null, 'scope' => null]);

        foreach ($filters as $filter) {
            $filter = $resolver->resolve($filter);
            $context = ['locale' => $filter['locale'], 'scope' => $filter['scope']];
            $productQueryBuilder->addFilter($filter['field'], $filter['operator'], $filter['value'], $context);
        }

        return $productQueryBuilder->execute();
    }

    /**
     * @return ProductQueryBuilderInterface
     */
    protected function getProductQueryBuilder()
    {
        return $this->pqbFactory->create();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [];
    }

    /**
     * Set channel
     *
     * @param string $channel
     *
     * @return FilterProductReader
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * Get channel
     *
     * @return string
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Return the job configuration
     *
     * @return JobInstance
     */
    protected function getJobConfiguration()
    {
        $jobInstance = $this->jobRepository->getJobManager()
            ->getRepository('AkeneoBatchBundle:JobInstance')
            ->findOneByCode($this->massEditType);

        $jobExecution    = $jobInstance->getJobExecutions()->last();
        $massEditJobConf = $this->massEditRepository->findOneByJobExecution($jobExecution);
        $configuration   = json_decode(stripcslashes($massEditJobConf->getConfiguration()), true);

        return $configuration;
    }
}
