<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Family;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class InMemoryFamilyRepository implements IdentifiableObjectRepositoryInterface, SaverInterface, FamilyRepositoryInterface
{
    /** @var Collection */
    private $families;

    public function __construct(array $families = [])
    {
        $this->families = new ArrayCollection($families);
    }

    public function getIdentifierProperties()
    {
        return ['code'];
    }

    public function findOneByIdentifier($identifier)
    {
        return $this->families->get($identifier);
    }

    public function save($object, array $options = [])
    {
        if (!$object instanceof FamilyInterface) {
            throw new \InvalidArgumentException('The object argument should be a family');
        }

        $this->families->set($object->getCode(), $object);
    }

    public function getFullRequirementsQB(FamilyInterface $family, $localeCode)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function getFullFamilies(FamilyInterface $family = null, ChannelInterface $channel = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findByIds(array $familyIds)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function hasAttribute($id, $attributeCode)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function find($id)
    {
        throw new NotImplementedException(__METHOD__);
    }

    public function findAll()
    {
        return $this->families->toArray();
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
