<?php

namespace Oro\Bundle\ImportExportBundle\Strategy;

class StrategyRegistry
{
    /**
     * Strategy storage format:
     * array(
     *     '<entityName>' => array(
     *         '<strategyAlias>' => <strategyObject>
     *     )
     * )
     *
     * @var array
     */
    protected $strategies = array();

    /**
     * @param StrategyInterface $strategy
     * @param string $entityName
     * @param string $alias
     * @throws \LogicException
     */
    public function addImportStrategy(StrategyInterface $strategy, $entityName, $alias)
    {
        if (empty($this->strategies[$entityName])) {
            $this->strategies[$entityName] = array();
        }

        if (!empty($this->strategies[$entityName][$alias])) {
            throw new \LogicException(
                sprintf('Strategy "%s" for entity %s already exists', $alias, $entityName)
            );
        }

        $this->strategies[$entityName][$alias] = $strategy;
    }

    /**
     * @param string $entityName
     * @param string $alias
     * @return StrategyInterface|null
     */
    public function getImportStrategy($entityName, $alias)
    {
        if (empty($this->strategies[$entityName]) || empty($this->strategies[$entityName][$alias])) {
            return null;
        }

        return $this->strategies[$entityName][$alias];
    }

    /**
     * @param string $entityName
     * @return StrategyInterface[]
     */
    public function getImportStrategiesByEntity($entityName)
    {
        if (empty($this->strategies[$entityName])) {
            return array();
        }

        return $this->strategies[$entityName];
    }
}
