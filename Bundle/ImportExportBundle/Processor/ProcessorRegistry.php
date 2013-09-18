<?php

namespace Oro\Bundle\ImportExportBundle\Processor;

use Oro\Bundle\ImportExportBundle\Exception\UnexpectedValueException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;

class ProcessorRegistry
{
    /**
     * Strategy storage format:
     * array(
     *     '<entityName>' => array(
     *         '<processorAlias>' => <processorObject>
     *     )
     * )
     *
     * @var array
     */
    protected $processors = array();

    /**
     * @param ProcessorInterface $processor
     * @param string $entityName
     * @param string $alias
     * @throws LogicException
     */
    public function registerProcessor(ProcessorInterface $processor, $entityName, $alias)
    {
        if (empty($this->processors[$entityName])) {
            $this->processors[$entityName] = array();
        }

        if (!empty($this->processors[$entityName][$alias])) {
            throw new LogicException(
                sprintf('Processor "%s" for entity "%s" already exists', $alias, $entityName)
            );
        }

        $this->processors[$entityName][$alias] = $processor;
    }
    /**
     * @param string $entityName
     * @param string $alias
     */
    public function unregisterProcessor($entityName, $alias)
    {
        unset($this->processors[$entityName][$alias]);
    }

    /**
     * Get processor by entity name and alias
     *
     * @param string $entityName
     * @param string $alias
     * @return ProcessorInterface
     * @throws UnexpectedValueException
     */
    public function getProcessor($entityName, $alias)
    {
        if (empty($this->processors[$entityName]) || empty($this->processors[$entityName][$alias])) {
            throw new UnexpectedValueException(
                sprintf('Processor "%s" for entity "%s" is not exist', $alias, $entityName)
            );
        }

        return $this->processors[$entityName][$alias];
    }

    /**
     * Checks if processor registered
     *
     * @param string $entityName
     * @param string $alias
     * @return bool
     */
    public function hasProcessor($entityName, $alias)
    {
        return !empty($this->processors[$entityName]) && !empty($this->processors[$entityName][$alias]);
    }

    /**
     * Get all processors by entity name
     *
     * @param string $entityName
     * @return ProcessorInterface[]
     */
    public function getProcessorsByEntity($entityName)
    {
        if (empty($this->processors[$entityName])) {
            return array();
        }

        return $this->processors[$entityName];
    }
}
