<?php

namespace Pim\Bundle\TransformBundle\Builder;

use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Create field names for associations and product values
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * Get the association field names
     *
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
