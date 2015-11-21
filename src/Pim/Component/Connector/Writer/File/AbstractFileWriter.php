<?php

namespace Pim\Component\Connector\Writer\File;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Abstract file writer to handle configuration-related logic.
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
    /**
     * @Assert\NotBlank(groups={"Execution"})
     * @WritableDirectory(groups={"Execution"})
     *
     * @var string
     */
    protected $filePath = '/tmp/export_%datetime%.csv';

    /** @var StepExecution */
    protected $stepExecution;

    /** @var string */
    protected $resolvedFilePath;

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
            $this->resolvedFilePath = strtr($this->filePath, ['%datetime%' => date('Y-m-d_H:i:s')]);
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
                    'label' => 'pim_base_connector.export.filePath.label',
                    'help'  => 'pim_base_connector.export.filePath.help'
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
