<?php

namespace Pim\Bundle\ImportExportBundle\Reader;

use Doctrine\ORM\EntityManager;

class DoctrineReader implements ReaderInterface
{
    protected $entityManager;
    protected $entity;

    public function __construct(EntityManager $entityManager, $entity)
    {
        $this->entityManager = $entityManager;
        $this->entity        = $entity;
    }

    public function read()
    {
        $repository = $this->entityManager->getRepository($this->entity);

        return $repository->findAll();
    }
}
