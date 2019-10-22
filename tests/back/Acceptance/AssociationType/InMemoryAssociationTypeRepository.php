<?php

namespace Akeneo\Test\Acceptance\AssociationType;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\Model\AssociationTypeInterface;
use Pim\Component\Catalog\Model\EntityWithAssociationsInterface;
use Pim\Component\Catalog\Model\ProductAssociationInterface;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;

class InMemoryAssociationTypeRepository implements AssociationTypeRepositoryInterface, SaverInterface
{
    /** @var AssociationTypeInterface[] */
    private $associationType;

    public function __construct()
    {
        $this->associationType = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function save($associationType, array $options = [])
    {
        if (!$associationType instanceof AssociationTypeInterface) {
            throw new \InvalidArgumentException('Only group objects are supported.');
        }
        $this->associationType->set($associationType->getCode(), $associationType);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        return $this->associationType->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findMissingAssociationTypes(EntityWithAssociationsInterface $entity)
    {
        $associations = $entity->getAssociations();
        $associationType = $this->associationType->filter(function (AssociationTypeInterface $associationType) use ($associations) {
            return !$associations->exists(function ($key, ProductAssociationInterface $productAssociation) use ($associationType) {
                return $productAssociation->getAssociationType()->getCode() === $associationType->getCode();
            });
        });

        return $associationType->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function countAll(): int
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
