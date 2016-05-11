<?php

namespace Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Pim\Bundle\EnrichBundle\Connector\Item\MassEdit\VariantGroupCleaner;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product reader for mass edit, skipping products not usable in variant group
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredVariantGroupProductReader extends FilteredProductReader
{
    /** @var VariantGroupCleaner */
    protected $cleaner;

    /** @var array */
    protected $cleanedFilters;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param VariantGroupCleaner                 $cleaner
     */
    public function __construct(ProductQueryBuilderFactoryInterface $pqbFactory, VariantGroupCleaner $cleaner)
    {
        parent::__construct($pqbFactory);
        $this->cleaner = $cleaner;
    }

    /**
     * Build filters to exclude products
     *
     * @return array|null
     */
    protected function getConfiguredFilters()
    {
        if (null === $this->cleanedFilters) {
            $jobParameters = $this->stepExecution->getJobParameters();
            $filters = $jobParameters->getParameter('filters');
            $actions = $jobParameters->getParameter('actions');
            $this->cleanedFilters = $this->cleaner->clean($this->stepExecution, $filters, $actions);
            var_dump($filters);
            var_dump($this->cleanedFilters);
        }

        return $this->cleanedFilters;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->isExecuted = false;
        $this->cleanedFilters = null;
    }
}
