<?php

namespace Akeneo\Component\Analytics;

use Akeneo\Component\Analytics\ChainedDataCollectorInterface;
use Akeneo\Component\Analytics\DataCollectorInterface;

/**
 * Aggregate data collected by registered collectors
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedDataCollector implements ChainedDataCollectorInterface
{
    /** @var DataCollectorInterface[] */
    protected $collectors = [];

    /**
     * {@inheritdoc}
     */
    public function addCollector(DataCollectorInterface $collector)
    {
        $this->collectors[] = $collector;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        $aggregatedData = [];
        foreach ($this->collectors as $collector) {
            $collectedData  = $collector->collect();
            $aggregatedData = $aggregatedData + $collectedData;
        }

        return $aggregatedData;
    }
}
