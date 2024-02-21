<?php

namespace Akeneo\Tool\Component\Connector\Analyzer;

use Akeneo\Tool\Component\Batch\Item\ItemReaderInterface;

/**
 * An analyzer is a tool to get statistics from a datasource
 * (files, like CSV, yml, or webservices).
 *
 * Reading the data source is provided by the reader
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AnalyzerInterface
{
    /**
     * Analyze a datasource and returns statistics about its content
     * @param ItemReaderInterface $reader
     *
     * @return array
     */
    public function analyze(ItemReaderInterface $reader);
}
