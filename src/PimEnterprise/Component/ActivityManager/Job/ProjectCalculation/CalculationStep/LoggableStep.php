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

use Box\Spout\Common\Type;
use Box\Spout\Writer\WriterFactory;
use Box\Spout\Writer\WriterInterface;
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

    /** @var WriterInterface */
    protected $csvWriter = null;

    public function __construct($fileLog)
    {
        $this->fileLog = $fileLog;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(ProductInterface $product, ProjectInterface $project)
    {
        if (null === $this->csvWriter) {
            $this->csvWriter = WriterFactory::create(Type::CSV);
            $this->csvWriter->openToFile($this->fileLog);
        }

        $this->csvWriter->addRow([$project->getCode(), $product->getId(), memory_get_usage()/1024/1024]);
    }
}
