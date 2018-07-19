<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Job\ProjectCalculation\CalculationStep;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\WorkOrganization\TeamWorkAssistant\Component\Model\ProjectInterface;

/**
 * Log the memory usage. Use it to debug.
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
