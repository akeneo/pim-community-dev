<?php

namespace Oro\Bundle\WorkflowBundle\Model\PostAction;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\EntityManager;

use Oro\Bundle\WorkflowBundle\Exception\InvalidParameterException;
use Oro\Bundle\WorkflowBundle\Exception\NotManageableEntityException;

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
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        $this->registry = $registry;
    }

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
        return $this->options[0];
    }
}
