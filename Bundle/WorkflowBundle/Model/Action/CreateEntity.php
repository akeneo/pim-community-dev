<?php

namespace Oro\Bundle\WorkflowBundle\Model\Action;

use Symfony\Component\PropertyAccess\PropertyPath;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;
use Oro\Bundle\WorkflowBundle\Exception\ActionException;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;

class CreateEntity extends AbstractAction
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
        $entity = $this->createEntity($context);
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

        if (empty($options['attribute'])) {
            throw new InvalidParameterException('Attribute name parameter is required');
        }
        if (!$options['attribute'] instanceof PropertyPath) {
            throw new InvalidParameterException('Attribute must be valid property definition.');
        }

        if (!empty($options['data']) && !is_array($options['data'])) {
            throw new InvalidParameterException('Entity data must be an array.');
        }

        $this->options = $options;

        return $this;
    }

    /**
     * @param mixed $context
     * @return mixed
     * @throws NotManageableEntityException
     * @throws ActionException
     */
    protected function createEntity($context)
    {
        $entityClassName = $this->getEntityClassName();
        $entityData = $this->getEntityData();

        /** @var EntityManager $entityManager */
        $entityManager = $this->registry->getManagerForClass($entityClassName);
        if (!$entityManager) {
            throw new NotManageableEntityException($entityClassName);
        }

        $entity = new $entityClassName();
        $this->assignEntityData($context, $entity, $entityData);

        try {
            $entityManager->persist($entity);
            $entityManager->flush($entity);
        } catch (\Exception $e) {
            throw new ActionException(
                sprintf('Can\'t create entity %s. %s', $entityClassName, $e->getMessage())
            );
        }

        return $entity;
    }

    /**
     * @param mixed $context
     * @param object $entity
     * @param array $parameters
     */
    protected function assignEntityData($context, $entity, array $parameters)
    {
        foreach ($parameters as $parameterName => $valuePath) {
            $parameterValue = $this->contextAccessor->getValue($context, $valuePath);
            $this->contextAccessor->setValue($entity, $parameterName, $parameterValue);
        }
    }

    /**
     * @return string
     */
    protected function getEntityClassName()
    {
        return $this->options['class'];
    }

    /**
     * @return array
     */
    protected function getEntityData()
    {
        return $this->getOption($this->options, 'data', array());
    }
}
