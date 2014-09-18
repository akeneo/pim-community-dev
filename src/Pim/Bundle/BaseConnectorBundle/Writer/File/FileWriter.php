<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Symfony\Component\Validator\Constraints as Assert;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\WritableDirectory;

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
     * @Assert\NotBlank(groups={"Execution"})
     * @WritableDirectory(groups={"Execution"})
     */
    protected $filePath = "%tmpdir%/export_%datetime%";

    /**
     * @var StepExecution
     */
    protected $stepExecution;

    private $handler;

    private $resolvedFilePath;

    /**
     * Set the file path
     *
     * @param string $filePath
     *
     * @return FileWriter
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
        if (!isset($this->resolvedFilePath)) {
            $this->resolvedFilePath = PathPatternResolver::resolve($this->filePath);
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
            if (false === fwrite($this->handler, $entry)) {
                throw new RuntimeErrorException('Failed to write to file %path%', ['%path%' => $this->getPath()]);
            } else {
                $this->stepExecution->incrementSummaryInfo('write');
            }
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
            'filePath' => array(
                'options' => array(
                    'label' => 'pim_base_connector.export.filePath.label',
                    'help'  => 'pim_base_connector.export.filePath.help'
                )
            )
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
