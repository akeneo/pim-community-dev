<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

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
     * @Assert\NotBlank(groups={"Execution"})
     */
    protected $filePath = '/tmp/export_%datetime%.csv';

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
            $this->resolvedFilePath = strtr(
                $this->filePath,
                array(
                    '%datetime%' => date('Y-m-d_H:i:s')
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
            $this->stepExecution->incrementSummaryInfo('write');
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
                    'label' => 'pim_import_export.export.filePath.label',
                    'help'  => 'pim_import_export.export.filePath.help'
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
