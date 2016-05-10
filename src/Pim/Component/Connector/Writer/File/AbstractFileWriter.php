<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
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
abstract class AbstractFileWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /** @var FilePathResolverInterface */
    protected $filePathResolver;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var string */
    protected $resolvedFilePath;

    /** @var array */
    protected $filePathResolverOptions;

    /** @var Filesystem */
    protected $localFs;

    /**
     * @param FilePathResolverInterface $filePathResolver
     */
    public function __construct(FilePathResolverInterface $filePathResolver)
    {
        $this->filePathResolver = $filePathResolver;
        $this->filePathResolverOptions = [
            'parameters' => ['%datetime%' => date('Y-m-d_H:i:s')]
        ];
        $this->localFs = new Filesystem();
    }

    /**
     * Get the file path
     *
     * @return string
     */
    public function getFilePath()
    {
        // TODO: why we need this?
        $parameters = $this->stepExecution->getJobParameters();

        return $parameters->getParameter('filePath');
    }

    /**
     * Get the file path in which to write the data
     *
     * @return string
     */
    public function getPath()
    {
        if (null === $this->resolvedFilePath) {
            $parameters = $this->stepExecution->getJobParameters();
            $filePath = $parameters->getParameter('filePath');

            if ($parameters->hasParameter('mainContext')){
                $mainContext = $parameters->getParameter('mainContext');
                foreach ($mainContext as $key => $value) {
                    $this->filePathResolverOptions['parameters']['%' . $key . '%'] = $value;
                }
            }

            $this->resolvedFilePath = $this->filePathResolver->resolve($filePath, $this->filePathResolverOptions);
        }

        return $this->resolvedFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
