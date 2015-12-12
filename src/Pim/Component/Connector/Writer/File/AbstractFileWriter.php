<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Item\ItemWriterInterface;
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

    /** @var string */
    protected $filePath = '/tmp/export_%datetime%.csv';

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
     * Set the file path
     *
     * @param string $filePath
     *
     * @return AbstractFileWriter
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        $this->resolvedFilePath = null;

        return $this;
    }

    /**
     * Get the file path
     *
     * @return string
     */
    public function getFilePath()
    {
        return $this->filePath;
    }

    /**
     * Get the file path in which to write the data
     *
     * @return string
     */
    public function getPath()
    {
        if (null === $this->resolvedFilePath) {
            $this->resolvedFilePath = $this->filePathResolver->resolve($this->filePath, $this->filePathResolverOptions);
        }

        return $this->resolvedFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return [
            'filePath' => [
                'options' => [
                    'label' => 'pim_connector.export.filePath.label',
                    'help'  => 'pim_connector.export.filePath.help'
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
