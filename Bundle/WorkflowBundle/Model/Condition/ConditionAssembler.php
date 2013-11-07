<?php

namespace Oro\Bundle\WorkflowBundle\Model\Condition;

use Oro\Bundle\WorkflowBundle\Model\Condition\ConditionFactory;
use Oro\Bundle\WorkflowBundle\Model\AbstractAssembler;
use Oro\Bundle\WorkflowBundle\Model\Pass\PassInterface;

class ConditionAssembler extends AbstractAssembler
{
    /**
     * @var ConditionFactory
     */
    protected $factory;

    /**
     * @var PassInterface
     */
    protected $configurationPass;

    /**
     * @param ConditionFactory $factory
     * @param PassInterface $configurationPass
     */
    public function __construct(ConditionFactory $factory, PassInterface $configurationPass)
    {
        $this->factory           = $factory;
        $this->configurationPass = $configurationPass;
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
        if (is_array($conditionParameters)) {
            foreach ($conditionParameters as $key => $conditionParameter) {
                if ($this->isService($conditionParameter) || $key == 'rules') {
                    $options[$key] = $this->assemble($conditionParameter);
                } else {
                    $options[$key] = $conditionParameter;
                }
            }
        } else {
            $options[] = $conditionParameters;
        }

        $passedOptions = $this->configurationPass->pass($options);

        $serviceName = $this->getServiceName($conditionType);
        return $this->factory->create($serviceName, $passedOptions);
    }
}
