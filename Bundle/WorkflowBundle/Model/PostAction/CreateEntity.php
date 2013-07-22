<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;

class CreateEntity extends AbstractPostAction
{
    /**
     * @var array
     */
    protected $options;

    /**
     * {@inheritdoc}
     */
    public function execute($context)
    {
        $entity = $this->createEntity();
        $this->contextAccessor->setValue($context, $this->options[1], $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (count($options) < 2) {
            throw new InvalidParameterException('Class name and property parameters are required');
        }
        $this->options = $options;
        return $this;
    }

    /**
     * Create entity.
     *
     * @return object
     */
    protected function createEntity()
    {
        $entityClassName = $this->getEntityClassName();
        return new $entityClassName();
    }

    /**
     * Get entity class name.
     *
     * @return string
     */
    protected function getEntityClassName()
    {
        return $this->options[0];
    }
}
