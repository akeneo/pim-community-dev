<?php

namespace Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Pim\Bundle\BaseConnectorBundle\Reader\ProductReaderInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
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

    /** @var CursorInterface */
    protected $products;

    /** @var bool */
    protected $isExecuted;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     */
    public function __construct(ProductQueryBuilderFactoryInterface $pqbFactory)
    {
        $this->pqbFactory = $pqbFactory;
        $this->isExecuted = false;
    }

    /**
     * {@inheritdoc}
     */
    public function read()
    {
        $filters = $this->getConfiguredFilters();
        if (null === $filters) {
            return null;
        }

        if (!$this->isExecuted) {
            $this->isExecuted = true;
            $this->products   = $this->getProductsCursor($filters);
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
        $this->isExecuted = false;
    }

    /**
     * @param array $filters
     *
     * @return CursorInterface
     */
    protected function getProductsCursor(array $filters)
    {
        $productQueryBuilder = $this->getProductQueryBuilder();

        $resolver = new OptionsResolver();
        $resolver->setRequired(['field', 'operator', 'value'])
            ->setDefined(['context'])
            ->setDefaults([
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
     * @return array|null
     */
    protected function getConfiguredFilters()
    {
        $jobParameters = $this->stepExecution->getJobParameters();

        return $jobParameters->get('filters');
    }
}
