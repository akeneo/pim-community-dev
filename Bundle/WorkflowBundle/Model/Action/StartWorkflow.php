<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Symfony\Component\PropertyAccess\PropertyPath;

use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\WorkflowManager;
use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class StartWorkflow extends AbstractAction
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var WorkflowManager
     */
    protected $workflowManager;

    /**
     * @param ContextAccessor $contextAccessor
     * @param WorkflowManager $workflowManager
     */
    public function __construct(ContextAccessor $contextAccessor, WorkflowManager $workflowManager)
    {
        parent::__construct($contextAccessor);

        $this->workflowManager = $workflowManager;
    }

    /**
     * Allowed options:
     *  - name - workflow name (can be an attribute)
     *  - attribute - property path used to save created workflow item
     *  - entity (optional) - attribute with entity used to start workflow
     *  - transition (optional) - start transition name (can be an attribute)
     *  - data (optional) - list of additional workflow item parameters
     *
     * {@inheritDoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['name'])) {
            throw new InvalidParameterException('Workflow name parameter is required');
        }

        if (empty($options['attribute'])) {
            throw new InvalidParameterException('Attribute name parameter is required');
        }
        if (!$options['attribute'] instanceof PropertyPath) {
            throw new InvalidParameterException('Attribute must be valid property definition');
        }

        if (!empty($options['entity']) && !$options['entity'] instanceof PropertyPath) {
            throw new InvalidParameterException('Entity must be valid property definition');
        }

        $this->options = $options;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function executeAction($context)
    {
        $workflowName = $this->getName($context);
        $entity = $this->getEntity($context);
        $startTransition = $this->getTransition($context);
        $data = $this->getData($context);

        $workflowItem = $this->workflowManager->startWorkflow($workflowName, $entity, $startTransition, $data);
        $attribute = $this->getAttribute();
        $this->contextAccessor->setValue($context, $attribute, $workflowItem);
    }

    /**
     * @param mixed $context
     * @return string
     */
    protected function getName($context)
    {
        return $this->contextAccessor->getValue($context, $this->options['name']);
    }

    /**
     * @return PropertyPath
     */
    protected function getAttribute()
    {
        return $this->options['attribute'];
    }

    /**
     * @param $context
     * @return null|object
     * @throws InvalidParameterException
     */
    protected function getEntity($context)
    {
        if (empty($this->options['entity'])) {
            return null;
        }

        $entity = $this->contextAccessor->getValue($context, $this->options['entity']);
        if (!is_object($entity)) {
            throw new InvalidParameterException('Entity value must be an object');
        }

        return $entity;
    }

    /**
     * @param mixed $context
     * @return string|null
     */
    protected function getTransition($context)
    {
        if (empty($this->options['transition'])) {
            return null;
        }

        return $this->contextAccessor->getValue($context, $this->options['transition']);
    }

    /**
     * @param mixed $context
     * @return array
     */
    protected function getData($context)
    {
        $data = $this->getOption($this->options, 'data', array());

        foreach ($data as $key => $value) {
            $data[$key] = $this->contextAccessor->getValue($context, $value);
        }

        return $data;
    }
}
