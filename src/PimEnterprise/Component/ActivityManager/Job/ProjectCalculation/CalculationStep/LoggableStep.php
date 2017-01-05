<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\ProjectCalculation\CalculationStep;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;

/**
 * Log the memory usage. It is enabled by default, use it to debug.
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class LoggableStep implements CalculationStepInterface
{
    /** @var string */
    protected $fileLog;

    /**
     * @param string $fileLog
     */
    public function __construct($fileLog)
    {
        $this->fileLog = $fileLog;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        $newRow = [$project->getCode(), $product->getId(), memory_get_usage()/1024/1024];
        $handle = fopen($this->fileLog, 'a+');
        fputcsv($handle, $newRow);
        fclose($handle);
    }
}
