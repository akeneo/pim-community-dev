<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Structure\Attribute;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeGroupInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAttributeRepository implements AttributeRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $attributes;

    /**
     * @param AttributeInterface[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = new ArrayCollection($attributes);
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
    public function findOneByIdentifier($identifier)
    {
        return $this->attributes->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save($attribute, array $options = [])
    {
        if (!$attribute instanceof AttributeInterface) {
            throw new \InvalidArgumentException('The object argument should be a attribute');
        }

        $this->attributes->set($attribute->getCode(), $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $attributes = [];
        foreach ($this->attributes as $attribute) {
            $keepThisAttribute = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($attribute->$getter() !== $value) {
                    $keepThisAttribute = false;
                }
            }

            if ($keepThisAttribute) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
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

    /**
     * {@inheritdoc}
     */
    public function findAllInDefaultGroup()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findUniqueAttributeCodes()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findMediaAttributeCodes()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllAxesQB()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributesAsArray($withLabel = false, $locale = null, array $ids = [])
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeIdsUseableInGrid($codes = null, $groupIds = null)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCode()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypeByCodes(array $codes)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCodesByType($type)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCodesByGroup(AttributeGroupInterface $group)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAttributesByFamily(FamilyInterface $family)
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findAvailableAxes($locale)
    {
        throw new NotImplementedException(__METHOD__);
    }
}
