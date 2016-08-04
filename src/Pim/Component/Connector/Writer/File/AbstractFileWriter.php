<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Abstract file writer to handle file naming and configuration-related logic.
 * write() method must be implemented by children.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFileWriter implements ItemWriterInterface, StepExecutionAwareInterface
{
    /** @var StepExecution */
    protected $stepExecution;

    /** @var Filesystem */
    protected $localFs;

    public function __construct()
    {
        $this->localFs = new Filesystem();
    }

    /**
     * Get the file path in which to write the data
     *
     * @return string
     */
    public function getPath()
    {
        $parameters = $this->stepExecution->getJobParameters();

        return $parameters->get('filePath');
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
