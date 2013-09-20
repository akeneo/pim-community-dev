<?php

namespace Oro\Bundle\ImportExportBundle\Processor;

use Oro\Bundle\ImportExportBundle\Exception\UnexpectedValueException;
use Oro\Bundle\ImportExportBundle\Exception\LogicException;

class ProcessorRegistry
{
    const TYPE_EXPORT = 'export';
    const TYPE_IMPORT = 'import';
    const TYPE_VALIDATE_IMPORT = 'validate_import';

    /**
     * Processor storage format:
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
     * @param string $type
     * @param string $alias
     * @throws LogicException
     */
    public function registerProcessor(ProcessorInterface $processor, $type, $entityName, $alias)
    {
        if (empty($this->processors[$type])) {
            $this->processors[$type] = array();
        }
        if (empty($this->processors[$type][$entityName])) {
            $this->processors[$type][$entityName] = array();
        }

        if (!empty($this->processors[$type][$entityName][$alias])) {
            throw new LogicException(
                sprintf('"%s" processor "%s" for entity "%s" already exists', $type, $alias, $entityName)
            );
        }

        $this->processors[$type][$entityName][$alias] = $processor;
    }

    /**
     * @param string $type
     * @param string $entityName
     * @param string $alias
     */
    public function unregisterProcessor($type, $entityName, $alias)
    {
        unset($this->processors[$type][$entityName][$alias]);
    }

    /**
     * Get processor by entity name and alias
     *
     * @param string $type
     * @param string $entityName
     * @param string $alias
     * @return ProcessorInterface
     * @throws UnexpectedValueException
     */
    public function getProcessor($type, $entityName, $alias)
    {
        if (empty($this->processors[$type][$entityName][$alias])) {
            throw new UnexpectedValueException(
                sprintf('"%s" processor "%s" for entity "%s" is not exist', $type, $alias, $entityName)
            );
        }

        return $this->processors[$type][$entityName][$alias];
    }

    /**
     * Checks if processor registered
     *
     * @param string $type
     * @param string $entityName
     * @param string|null $alias
     * @return bool
     */
    public function hasProcessor($type, $entityName, $alias = null)
    {
        if (empty($this->processors[$type][$entityName])) {
            return false;
        }
        return (null === $alias) ? true : !empty($this->processors[$type][$entityName]);
    }

    /**
     * Get all processors by entity name
     *
     * @param string $type
     * @param string $entityName
     * @return ProcessorInterface[]
     */
    public function getProcessorsByEntity($type, $entityName)
    {
        if (empty($this->processors[$type][$entityName])) {
            return array();
        }

        return $this->processors[$type][$entityName];
    }

    /**
     * Get all processors by entity name
     *
     * @param string $type
     * @param string $entityName
     * @return array
     */
    public function getProcessorAliasesByEntity($type, $entityName)
    {
        if (empty($this->processors[$type][$entityName])) {
            return array();
        }

        return array_keys($this->processors[$type][$entityName]);
    }
}
