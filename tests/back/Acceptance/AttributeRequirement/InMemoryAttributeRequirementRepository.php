<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\AttributeRequirement;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Test\Acceptance\Common\NotImplementedException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Repository\AttributeRequirementRepositoryInterface;

/**
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryAttributeRequirementRepository implements AttributeRequirementRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $attributeRequirements;

    /** @var string */
    private $className;

    /**
     * @param array  $attributeRequirements
     * @param string $className
     */
    public function __construct(array $attributeRequirements, string $className)
    {
        $this->attributeRequirements = new ArrayCollection($attributeRequirements);
        $this->className = $className;
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
        return $this->attributeRequirements->get($identifier);
    }

    /**
     * {@inheritdoc}
     */
    public function save($attributeRequirement, array $options = [])
    {
        if (!$attributeRequirement instanceof AttributeRequirementInterface) {
            throw new \InvalidArgumentException('The object argument should be an attribute requirement');
        }

        $this->attributeRequirements->set($attributeRequirement->getCode(), $attributeRequirement);
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
        throw new NotImplementedException(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $attributeGroups = [];
        foreach ($this->attributeRequirements as $attributeGroup) {
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
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function findRequiredAttributesCodesByFamily(FamilyInterface $family)
    {
        throw new NotImplementedException(__METHOD__);
    }
}
