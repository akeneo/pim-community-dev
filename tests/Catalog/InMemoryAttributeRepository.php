<?php

declare(strict_types = 1);

namespace Akeneo\Test\Integration\Catalog;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\ORM\QueryBuilder;
use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 *
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class InMemoryAttributeRepository implements
    AttributeRepositoryInterface,
    SaverInterface
{
    /** @var \ArrayObject */
    private $attributes;

    public function __construct()
    {
        $this->attributes = new \ArrayObject();
    }

    /**
     * {@inheritdoc}
     */
    public function save($attribute, array $options = [])
    {
        $this->attributes->append($attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        foreach($this->attributes as $attribute) {
            if (AttributeTypes::IDENTIFIER === $attribute->getType()) {
                return $attribute;
            }
        }

        return null;
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

    public function findOneByIdentifier($identifier)
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
