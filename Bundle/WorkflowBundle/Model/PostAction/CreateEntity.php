<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;
use Oro\Bundle\WorkflowBundle\Model\ContextAccessor;
use Symfony\Component\PropertyAccess\PropertyPath;

class CreateEntity extends AbstractPostAction
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
    public function execute($context)
    {
        $entity = $this->createEntity();
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

        $this->options = $options;

        return $this;
    }

    /**
     * @return object
     * @throws NotManageableEntityException
     */
    protected function createEntity()
    {
        $entityClassName = $this->getEntityClassName();
        /** @var EntityManager $entityManager */
        $entityManager = $this->registry->getManagerForClass($entityClassName);
        if (!$entityManager) {
            throw new NotManageableEntityException($entityClassName);
        }

        $entity = new $entityClassName();
        $entityManager->persist($entity);
        $entityManager->flush($entity);

        return $entity;
    }

    /**
     * @return string
     */
    protected function getEntityClassName()
    {
        return $this->options['class'];
    }
}
