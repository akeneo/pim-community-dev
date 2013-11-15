<?php

namespace Pim\Bundle\ImportExportBundle\Writer\File;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\BatchBundle\Item\ItemWriterInterface;
use Oro\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Oro\Bundle\BatchBundle\Entity\StepExecution;
use Oro\Bundle\BatchBundle\Step\StepExecutionAwareInterface;

/**
 * Write data into a file on the filesystem
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileWriter extends AbstractConfigurableStepElement implements
    ItemWriterInterface,
    StepExecutionAwareInterface
{
    /**
     * @Assert\NotBlank
     */
    protected $directoryName;

    /**
     * @Assert\NotBlank
     */
    protected $fileName = 'export_%datetime%.csv';

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    private $handler;

    private $resolvedFilePath;

    /**
     * Set the filename
     *
     * @param string $fileName
     *
     * @return FileWriter
     */
    public function setFileName($fileName)
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Get the filename
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->fileName;
    }

    /**
     * Set the directory name
     *
     * @param string $directoryName
     *
     * @return FileWriter
     */
    public function setDirectoryName($directoryName)
    {
        $this->directoryName = $directoryName;

        return $this;
    }

    /**
     * Get the directory name
     *
     * @return string
     */
    public function getDirectoryName()
    {
        return $this->directoryName;
    }

    /**
     * Get the file path in which to write the data
     *
     * @return string
     */
    public function getPath()
    {
        if (!isset($this->resolvedFilePath)) {
            return sprintf(
                '%s/%s',
                $this->directoryName,
                strtr(
                    $this->fileName,
                    array(
                        '%datetime%' => date('Y-m-d_H:i:s')
                    )
                )
            );
        }

        return $this->resolvedFilePath;
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $data)
    {
        if (!$this->handler) {
            $path = $this->getPath();
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            $this->handler = fopen($path, 'w');
        }

        foreach ($data as $entry) {
            fwrite($this->handler, $entry);
            $this->stepExecution->incrementWriteCount();
        }
    }

    /**
     * Close handler when desctructing the current instance
     */
    public function __destruct()
    {
        if ($this->handler) {
            fclose($this->handler);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurationFields()
    {
        return array(
            'directoryName' => array(),
            'fileName' => array()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;
    }
}
