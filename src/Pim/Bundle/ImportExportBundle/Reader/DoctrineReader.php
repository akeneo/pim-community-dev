<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;

class DoctrineReader implements ReaderInterface
{
    protected $entityManager;
    protected $entity;
    protected $methodName;
    protected $methodParams;

    public function __construct(EntityManager $entityManager, $entity, $methodName = null, array $methodParams = array())
    {
        $this->entityManager = $entityManager;
        $this->entity        = $entity;
        $this->methodName    = $methodName;
        $this->methodParams  = $methodParams;
    }

    public function read()
    {
        $repository = $this->entityManager->getRepository($this->entity);

        if ($this->methodName) {
            return call_user_func_array(array($repository, $this->methodName), $this->methodParams);
        }

        return $repository->findAll();
    }
}
