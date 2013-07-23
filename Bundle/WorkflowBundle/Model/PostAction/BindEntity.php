<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Oro\Bundle\WorkflowBundle\Model\EntityBinder;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Symfony\Component\PropertyAccess\PropertyPath;

class BindEntity extends AbstractPostAction
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var EntityBinder
     */
    protected $entityBinder;

    /**
     * @param ContextAccessor $contextAccessor
     * @param EntityBinder $entityBinder
     */
    public function __construct(ContextAccessor $contextAccessor, EntityBinder $entityBinder)
    {
        parent::__construct($contextAccessor);

        $this->entityBinder = $entityBinder;
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['attribute'])) {
            throw new InvalidParameterException('Attribute name parameter is required');
        } elseif (!($options['attribute'] instanceof PropertyPath)) {
            throw new InvalidParameterException('Attribute name parameter must be instance of PropertyPath');
        }

        $this->options = $options;

        return $this;
    }

    /**
     * @return string|null
     */
    protected function getStep()
    {
        return !empty($this->options['step']) ? $this->options['step'] : null;
    }

    /**
     * @param WorkflowItem $context
     */
    public function execute($context)
    {
        $entity = $this->contextAccessor->getValue($context, $this->options['attribute']);
        $this->entityBinder->bind($context, $entity, $this->getStep());
    }
}
