<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Pim\Bundle\CatalogBundle\Entity\Repository\AssociationTypeRepository;

class AssociationTypeManager
{
    protected $repository;

    public function __construct(AssociationTypeRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getAssociationTypes()
    {
        return $this->repository->findAll();
    }
}
