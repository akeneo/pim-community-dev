<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\AttributeGroup;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAttributeGroupRepository implements AttributeGroupRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $attributeGroups;

    /**
     * @param AttributeGroupInterface[] $attributeGroups
     */
    public function __construct(array $attributeGroups = [])
    {
        $this->attributeGroups = new ArrayCollection($attributeGroups);
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
        return $this->attributeGroups->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save($attributeGroup, array $options = [])
    {
        if (!$attributeGroup instanceof AttributeGroupInterface) {
            throw new \InvalidArgumentException('The object argument should be a attribute group');
        }

        $this->attributeGroups->set($attributeGroup->getCode(), $attributeGroup);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdToLabelOrderedBySortOrder()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findDefaultAttributeGroup()
    {
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSortOrder()
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
        return $this->attributeGroups->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $attributeGroups = [];
        foreach ($this->attributeGroups as $attributeGroup) {
            $keepThisAttributeGroup = true;
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));
                if ($attributeGroup->$getter() !== $value) {
                    $keepThisAttributeGroup = false;
                }
            }

            if ($keepThisAttributeGroup) {
                $attributeGroups[] = $attributeGroup;
            }
        }

        return $attributeGroups;
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
