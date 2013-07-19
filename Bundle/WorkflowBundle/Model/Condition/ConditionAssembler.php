<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory;

class ConditionAssembler
{
    /**
     * @var ConditionFactory
     */
    protected $factory;

    public function __construct(ConditionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * @param array $configuration
     * @return null|ConditionInterface
     */
    public function assemble(array $configuration)
    {
        if (!$this->isService($configuration)) {
            return null;
        }

        $options = array();
        $conditionType = $this->getConditionType($configuration);
        $conditionParameters = $this->getConditionParameters($configuration);
        if (is_array($conditionParameters)) {
            foreach ($conditionParameters as $key => $conditionParameter) {
                if ($this->isService($conditionParameter)) {
                    $options[$key] = $this->assemble($conditionParameter);
                } else {
                    $options[$key] = $conditionParameter;
                }
            }
        } else {
            $options[] = $conditionParameters;
        }
        return $this->factory->create($conditionType, $options);
    }

    /**
     * Get condition name.
     *
     * @param array $configuration
     * @return string
     */
    protected function getConditionType(array $configuration)
    {
        $keys = array_keys($configuration);
        return $keys[0];
    }

    /**
     * Get condition parameters.
     *
     * @param array $configuration
     * @return mixed
     */
    protected function getConditionParameters(array $configuration)
    {
        $values = array_values($configuration);
        return $values[0];
    }

    /**
     * Check that configuration is a condition configuration.
     *
     * @param mixed $configuration
     * @return bool
     */
    protected function isService($configuration)
    {
        if (!is_array($configuration) || count($configuration) != 1) {
            return false;
        }
        return strpos($this->getConditionType($configuration), '@') === 0;
    }
}
