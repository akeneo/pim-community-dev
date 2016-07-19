<?php

namespace Pim\Bundle\EnrichBundle\Connector\Reader\MassEdit;

use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Pim\Bundle\EnrichBundle\Connector\Item\MassEdit\VariantGroupCleaner;
use Pim\Component\Catalog\Converter\MetricConverter;
use Pim\Component\Catalog\Manager\CompletenessManager;
use Pim\Component\Catalog\Query\ProductQueryBuilderFactoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Connector\Reader\Database\ProductReader;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Product reader for mass edit, skipping products not usable in variant group.
 *
 * This class is used to only skip duplicated elements and not throw exception during step execution.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilteredVariantGroupProductReader extends ProductReader
{
    /** @var VariantGroupCleaner */
    protected $cleaner;

    /** @var array */
    protected $cleanedFilters;

    /**
     * @param ProductQueryBuilderFactoryInterface $pqbFactory
     * @param VariantGroupCleaner                 $cleaner
     */
    public function __construct(
        ProductQueryBuilderFactoryInterface $pqbFactory,
        ChannelRepositoryInterface $channelRepository,
        CompletenessManager $completenessManager,
        MetricConverter $metricConverter,
        ObjectDetacherInterface $objectDetacher,
        $generateCompleteness,
        VariantGroupCleaner $cleaner
    ) {
        parent::__construct(
            $pqbFactory,
            $channelRepository,
            $completenessManager,
            $metricConverter,
            $objectDetacher,
            $generateCompleteness
        );

        $this->cleaner = $cleaner;
    }

    /**
     * {@inheritdoc}
     *
     * Get the configured filters, but remove duplicated products of variant groups before.
     *
     * This function is different from parent getConfiguredFilters, because it returns null when there is no matching
     * products after cleaning.
     *
     * @return array|null
     */
    protected function getConfiguredFilters()
    {
        if (null === $this->cleanedFilters) {
            $filters = parent::getConfiguredFilters();

            $jobParameters = $this->stepExecution->getJobParameters();
            $actions = $jobParameters->get('actions');

            $this->cleanedFilters = $this->cleaner->clean($this->stepExecution, $filters, $actions);
        }

        return $this->cleanedFilters;
    }
}
