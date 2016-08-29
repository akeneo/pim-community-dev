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

    /** @var FileExporterPathGeneratorInterface */
    protected $filePathGenerator;

    /** @var string Datetime format for the file path placeholder */
    protected $datetimeFormat = 'Y-m-d H-i-s';

    /**
     * @param FileExporterPathGeneratorInterface $filePathGenerator
     */
    public function __construct(FileExporterPathGeneratorInterface $filePathGenerator)
    {
        $this->filePathGenerator = $filePathGenerator;
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
        $filePath = $parameters->get('filePath');
        $resolvedFilePath = $this->filePathGenerator->generate(
            $filePath,
            [
                'parameters' => [
                    '%datetime%'  => date($this->datetimeFormat),
                    '%job_label%' => $this->stepExecution->getJobExecution()->getJobInstance()->getLabel()
                ]
            ]
        );

        return $resolvedFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
