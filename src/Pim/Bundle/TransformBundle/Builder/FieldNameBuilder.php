<?php

namespace Pim\Bundle\TransformBundle\Builder;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class FieldNameBuilder
{
    /** @var ManagerRegistry */
    protected $managerRegistry;

    /** @var string */
    protected $assocTypeClass;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param string          $assocTypeClass
     */
    public function __construct(ManagerRegistry $managerRegistry, $assocTypeClass)
    {
        $this->managerRegistry = $managerRegistry;
        $this->assocTypeClass  = $assocTypeClass;
    }

    /**
     * @return array
     */
    public function getAssociationFieldNames()
    {
        $fieldNames = [];
        foreach ($this->getAssociationTypes() as $assocType) {
            $fieldNames[] = $assocType->getCode() .'-groups';
            $fieldNames[] = $assocType->getCode() .'-products';
        }

        return $fieldNames;
    }

    /**
     * @param string $entityClass
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($entityClass)
    {
        return $this->managerRegistry->getRepository($entityClass);
    }

    /**
     * @return AssociationType[]
     */
    protected function getAssociationTypes()
    {
        return $this->getRepository($this->assocTypeClass)->findAll();
    }
}
