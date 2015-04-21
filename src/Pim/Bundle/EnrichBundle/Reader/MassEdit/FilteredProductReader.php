<?php

namespace Pim\Bundle\EnrichBundle\Reader\MassEdit;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Job\JobRepositoryInterface;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderFactoryInterface;
use Pim\Bundle\CatalogBundle\Query\ProductQueryBuilderInterface;
use Pim\Bundle\EnrichBundle\Entity\Repository\MassEditRepositoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product reader for mass edit, using the cursor and the pim filters
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredProductReader extends AbstractConfigurableStepElement implements ProductReaderInterface
{
    /** @var ProductQueryBuilderFactoryInterface */
    protected $pqbFactory;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var ObjectManager */
    protected $objectManager;

    /** @var CursorInterface */
    protected $products;

    /** @var bool */
    protected $isExecuted = false;

    /** @var string */
    protected $channel;

    /** @var JobRepositoryInterface */
    protected $jobRepository;

    /** @var string */
    protected $massEditType;

    /** @var MassEditRepositoryInterface */
    protected $massEditRepository;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param ObjectManager                       $objectManager
     * @param JobRepositoryInterface              $jobRepository
     * @param MassEditRepositoryInterface         $massEditRepository
     * @param string                              $massEditType
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ObjectManager $objectManager,
        JobRepositoryInterface $jobRepository,
        MassEditRepositoryInterface $massEditRepository,
        $massEditType
    ) {
        $this->pqbFactory         = $pqbFactory;
        $this->objectManager      = $objectManager;
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

        if (!$this->isExecuted) {
            $this->isExecuted = true;
            $this->products = $this->getProductsCursor($configuration['filters']);
            $this->products->next();
        }

        $result = $this->products->current();

        if (!empty($result)) {
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
        $this->objectManager->clear();
        $this->isExecuted = false;
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
        $resolver->setOptional(['context']);
        $resolver->setDefaults([
            'context' => ['locale' => null, 'scope' => null]
        ]);

        foreach ($filters as $filter) {
            $filter = $resolver->resolve($filter);
            $productQueryBuilder->addFilter(
                $filter['field'],
                $filter['operator'],
                $filter['value'],
                $filter['context']
            );
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
     * {@inheritdoc}
     */
    public function setChannel($channel)
    {
        $this->channel = $channel;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChannel()
    {
        return $this->channel;
    }

    /**
     * Return the job configuration
     *
     * @return array
     */
    protected function getJobConfiguration()
    {
        $jobExecution    = $this->stepExecution->getJobExecution();
        $massEditJobConf = $this->massEditRepository->findOneBy(['jobExecution' => $jobExecution]);

        return json_decode(stripcslashes($massEditJobConf->getConfiguration()), true);
    }
}
