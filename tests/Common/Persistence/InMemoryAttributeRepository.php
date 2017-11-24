<?php

declare(strict_types = 1);

namespace Pim\Test\Common\Persistence;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * In-memory implementation of an attribute repository for testing purpose.
 * Not all methods are implemented, do it as soon as you need it.
 *
 * The simplest way to use it is to inject it instead of attribute saver and repo using the DI.
 *
 * TODO: Add unit tests for this.
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class InMemoryAttributeRepository implements AttributeRepositoryInterface, SaverInterface
{
    /** @var ArrayCollection */
    private $attributes;

    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     *
     * TODO: At some point we'll need to generate ids for new objects. Maybe use it as a key ?
     */
    public function save($attribute, array $options = [])
    {
        if (!$attribute instanceof AttributeInterface) {
            throw new \InvalidArgumentException('This repository can only store attribute objects.');
        }

        if ($this->attributes->contains($attribute)) {
            throw new \Exception('This attribute is already stored.');
        }

        $this->attributes->add($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        $matchingAttributes = $this->attributes->filter(
            function (AttributeInterface $attribute) {
                return AttributeTypes::IDENTIFIER === $attribute->getType();
            }
        );

        if (0 === count($matchingAttributes)) {
            return null;
        }

        return $matchingAttributes->first();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($identifier)
    {
        $matchingAttributes = $this->attributes->filter(
            function (AttributeInterface $attribute) use ($identifier) {
                return $identifier === $attribute->getCode();
            }
        );

        if (0 === count($matchingAttributes)) {
            return null;
        }

        return $matchingAttributes->first();
    }

    /*********************************
     * NOT IMPLEMENTED YET
     ********************************/

    public function findAllInDefaultGroup()
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function findUniqueAttributeCodes()
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function findMediaAttributeCodes()
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function findAllAxesQB()
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function getAttributesAsArray($withLabel = false, $locale = null, array $ids = [])
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function getAttributeIdsUseableInGrid($codes = null, $groupIds = null)
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function getIdentifierCode()
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function getAttributeTypeByCodes(array $codes)
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function getAttributeCodesByType($type)
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function getAttributeCodesByGroup(AttributeGroupInterface $group)
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function findAttributesByFamily(FamilyInterface $family)
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function countAll()
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function findAvailableAxes($locale)
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function getIdentifierProperties()
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function find($id)
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function findAll()
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function findOneBy(array $criteria)
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }

    public function getClassName()
    {
        throw new \RuntimeException(__METHOD__.' not implemented.');
    }
}
