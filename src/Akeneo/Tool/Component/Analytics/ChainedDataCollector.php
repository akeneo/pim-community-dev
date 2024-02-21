<?php

namespace Akeneo\Tool\Component\Analytics;

/**
 * Aggregate data collected by registered collectors
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ChainedDataCollector
{
    const DEFAULT_COLLECTOR_TYPE = 'default';

    /** @var DataCollectorInterface[][] */
    protected $collectors = [];

    /**
     * @param DataCollectorInterface $collector
     * @param string                 $type
     */
    public function addCollector(DataCollectorInterface $collector, $type = self::DEFAULT_COLLECTOR_TYPE)
    {
        $this->collectors[$type][] = $collector;
    }

    /**
     * Collect aggregated data from collectors of the specified type.
     *
     * @param string $type
     *
     * @return array
     */
    public function collect($type)
    {
        $aggregatedData = [];
        foreach ($this->collectors[$type] as $collector) {
            $aggregatedData += $collector->collect();
        }

        return $aggregatedData;
    }
}
