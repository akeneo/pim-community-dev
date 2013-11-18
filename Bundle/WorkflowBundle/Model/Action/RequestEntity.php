<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Symfony\Component\PropertyAccess\PropertyPath;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;
use Oro\Bundle\WorkflowBundle\Exception\ActionException;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class RequestEntity extends AbstractAction
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var ManagerRegistry
     */
    protected $registry;

    /**
     * @param ContextAccessor $contextAccessor
     * @param ManagerRegistry $registry
     */
    public function __construct(ContextAccessor $contextAccessor, ManagerRegistry $registry)
    {
        parent::__construct($contextAccessor);

        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeAction($context)
    {
        $entity = $this->getEntityReference($context);
        $this->contextAccessor->setValue($context, $this->options['attribute'], $entity);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        if (empty($options['class'])) {
            throw new InvalidParameterException('Class name parameter is required');
        }

        if (empty($options['identifier'])) {
            throw new InvalidParameterException('Identifier parameter is required');
        }

        if (empty($options['attribute'])) {
            throw new InvalidParameterException('Attribute name parameter is required');
        }
        if (!$options['attribute'] instanceof PropertyPath) {
            throw new InvalidParameterException('Attribute must be valid property definition.');
        }

        $this->options = $options;

        return $this;
    }

    /**
     * Returns entity proxy for specified entity with specified ID
     *
     * @param mixed $context
     * @return object
     * @throws \Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException
     */
    protected function getEntityReference($context)
    {
        $entityClassName = $this->getEntityClassName();
        $entityIdentifier = $this->getEntityIdentifier($context);

        /** @var EntityManager $entityManager */
        $entityManager = $this->registry->getManagerForClass($entityClassName);
        if (!$entityManager) {
            throw new NotManageableEntityException($entityClassName);
        }

        return $entityManager->getReference($entityClassName, $entityIdentifier);
    }

    /**
     * @return string
     */
    protected function getEntityClassName()
    {
        return $this->options['class'];
    }

    /**
     * @param mixed $context
     * @return int|string|array
     */
    protected function getEntityIdentifier($context)
    {
        $identifier = $this->options['identifier'];

        if (is_array($identifier)) {
            foreach ($identifier as $key => $value) {
                $identifier[$key] = $this->contextAccessor->getValue($context, $value);
            }
        } else {
            $identifier = $this->contextAccessor->getValue($context, $identifier);
        }

        return $identifier;
    }
}
