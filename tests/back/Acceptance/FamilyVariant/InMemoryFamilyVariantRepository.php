<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\FamilyVariant;

use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Repository\FamilyVariantRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;

class InMemoryFamilyVariantRepository implements SaverInterface, IdentifiableObjectRepositoryInterface, FamilyVariantRepositoryInterface
{
    /** @var ArrayCollection|FamilyVariant[] */
    private $familyVariants;

    public function __construct(array $familyVariants = [])
    {
        $this->familyVariants = new ArrayCollection($familyVariants);
    }

    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->familyVariants->get($identifier);
    }

    public function save($object, array $options = [])
    {
        if (!$object instanceof FamilyVariant) {
            throw new \InvalidArgumentException('The object argument should be a family variant');
        }

        $this->familyVariants->set($object->getCode(), $object);
    }

    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findAll()
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findOneBy(array $criteria)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getClassName()
    {
        throw new NotImplementedException(__METHOD__);
    }
}
