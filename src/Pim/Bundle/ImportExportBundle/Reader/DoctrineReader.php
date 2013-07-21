<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

class DoctrineReader implements ReaderInterface
{
    protected $repository;
    protected $methodName;
    protected $methodParams;

    public function __construct($entityManager, $entity, $methodName = null, array $methodParams = array())
    {
        if ($entityManager instanceof EntityManager) {
            $this->repository = $entityManager->getRepository($entity);
        } elseif ($entityManager instanceof FlexibleManager) {
            $this->repository = $entityManager->getFlexibleRepository();
        } else {
            throw new \InvalidArgumentException(
                sprintf(
                    'Entity manager should be an instance of Doctrine\ORM\EntityManager '.
                    'or Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager, "%s" given.',
                    get_class($entityManager)
                )
            );
        }

        $this->methodName   = $methodName;
        $this->methodParams = $methodParams;
    }

    public function read()
    {
        if ($this->methodName) {
            return call_user_func_array(array($this->repository, $this->methodName), $this->methodParams);
        }

        return $this->repository->findAll();
    }
}
