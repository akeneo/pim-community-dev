<?php

namespace Akeneo\Component\Memory;

/**
 * Provides memory usage, max memory usage and memory limit in bytes
 *
 * Inspired by Symfony\Component\HttpKernel\DataCollector\MemoryDataCollector but decoupled of Request to be used in
 * commands
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MemoryUsageProvider
{
    /**
     * @return int the memory limit in bytes.
     */
    public function getLimit()
    {
        return $this->convertToBytes(ini_get('memory_limit'));
    }

    /**
     * @return int the memory usage in bytes.
     */
    public function getUsage()
    {
        return memory_get_usage(true);
    }

    /**
     * @return int the peak memory usage in bytes.
     */
    public function getPeakUsage()
    {
        return memory_get_peak_usage(true);
    }

    /***
     * @param $memoryLimit
     *
     * @return int|string
     */
    protected function convertToBytes($memoryLimit)
    {
        if ('-1' === $memoryLimit) {
            return -1;
        }

        $memoryLimit = strtolower($memoryLimit);
        $max = strtolower(ltrim($memoryLimit, '+'));
        if (0 === strpos($max, '0x')) {
            $max = intval($max, 16);
        } elseif (0 === strpos($max, '0')) {
            $max = intval($max, 8);
        } else {
            $max = (int) $max;
        }

        switch (substr($memoryLimit, -1)) {
            case 't': $max *= 1024;
            case 'g': $max *= 1024;
            case 'm': $max *= 1024;
            case 'k': $max *= 1024;
        }

        return $max;
    }
}
