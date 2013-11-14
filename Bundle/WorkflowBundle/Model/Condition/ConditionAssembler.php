<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\AbstractAssembler;

class ConditionAssembler extends AbstractAssembler
{
    const PARAMETERS_KEY = 'parameters';
    const MESSAGE_KEY = 'message';

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
        $conditionParameters = $this->parseParameters($conditionParameters);
        if (is_array($conditionParameters[self::PARAMETERS_KEY])) {
            foreach ($conditionParameters[self::PARAMETERS_KEY] as $key => $conditionParameter) {
                if ($this->isService($conditionParameter)) {
                    $options[$key] = $this->assemble($conditionParameter);
                } else {
                    $options[$key] = $conditionParameter;
                }
            }
        } else {
            $options[] = $conditionParameters[self::PARAMETERS_KEY];
        }

        $message = null;
        if (isset($conditionParameters[self::MESSAGE_KEY])) {
            $message = $conditionParameters[self::MESSAGE_KEY];
        } elseif (isset($options[self::MESSAGE_KEY])) {
            $message = $options[self::MESSAGE_KEY];
            unset($options[self::MESSAGE_KEY]);
        }
        $passedOptions = $this->passConfiguration($options);

        $serviceName = $this->getServiceName($conditionType);
        return $this->factory->create($serviceName, $passedOptions, $message);
    }

    /**
     * @param array $conditionParameters
     * @return array
     */
    protected function parseParameters($conditionParameters)
    {
        $result = array();
        if (isset($conditionParameters[self::PARAMETERS_KEY])) {
            $result[self::PARAMETERS_KEY] = $conditionParameters[self::PARAMETERS_KEY];
            if (isset($conditionParameters[self::MESSAGE_KEY])) {
                $result[self::MESSAGE_KEY] = $conditionParameters[self::MESSAGE_KEY];
            }
        } else {
            $result[self::PARAMETERS_KEY] = $conditionParameters;
        }
        return $result;
    }
}
