<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\AbstractAssembler;

class ConditionAssembler extends AbstractAssembler
{
    /**
     * @var ConditionFactory
     */
    protected $factory;

    /**
     * @param ConditionFactory $factory
     */
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
        $conditionType = $this->getEntityType($configuration);
        $conditionParameters = $this->getEntityParameters($configuration);
        $conditionParameters = $this->parseRules($conditionParameters);
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

        $message = null;
        if (isset($options['message'])) {
            $message = $options['message'];
            unset($options['message']);
        }
        $passedOptions = $this->passConfiguration($options);

        $serviceName = $this->getServiceName($conditionType);
        return $this->factory->create($serviceName, $passedOptions, $message);
    }

    /**
     * @param array $conditionParameters
     * @return array
     */
    protected function parseRules($conditionParameters)
    {
        $result = $conditionParameters;
        if (isset($conditionParameters['rules'])) {
            $result = $conditionParameters['rules'];
            if (isset($conditionParameters['message'])) {
                $result['message'] = $conditionParameters['message'];
            }
        }
        return $result;
    }
}
