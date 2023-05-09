<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Attribute;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
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
class InMemoryAttributeRepository implements AttributeRepositoryInterface, SaverInterface
{
    /** @var Collection */
    private $attributes;

    /**
     * @param AttributeInterface[] $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->attributes = new ArrayCollection();
        foreach ($attributes as $attribute) {
            $this->attributes->set($attribute->getCode(), $attribute);
        }
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
            foreach ($criteria as $key => $value) {
                $getter = sprintf('get%s', ucfirst($key));

                if (! is_array($value)) {
                    $value = [$value];
                }

                foreach ($value as $criteriaValue) {
                    if ($attribute->$getter() === $criteriaValue) {
                        $attributes[] = $attribute;
                    }
                }
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
        return $this->attributes->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        return $this->findBy($criteria, null, 1)[0] ?? null;
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
        $attributeCodes = [];
        /** @var AttributeInterface $attribute */
        foreach ($this->attributes as $attribute) {
            if (AttributeTypes::BACKEND_TYPE_MEDIA === $attribute->getBackendType()) {
                $attributeCodes[] = $attribute->getCode();
            }
        }

        return $attributeCodes;
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
    public function getIdentifier(): ?AttributeInterface
    {
        $attribute = $this->attributes->filter(function (AttributeInterface $attribute): bool {
            return  $attribute->getType() === AttributeTypes::IDENTIFIER && $attribute->isMainIdentifier();
        })->first();

        if (false === $attribute) {
            $attribute = $this->attributes->filter(function (AttributeInterface $attribute): bool {
                return  $attribute->getType() === AttributeTypes::IDENTIFIER && 'sku' === $attribute->getCode();
            })->first();
        }

        return false !== $attribute ? $attribute : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierCode(): ?string
    {
        $identifierAttribute = $this->getIdentifier();

        return $identifierAttribute?->getCode();
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeTypeByCodes(array $codes)
    {
        $types = [];
        foreach ($codes as $code) {
            $attribute = $this->attributes->get($code);
            if (null !== $attribute) {
                $types[$code] = $attribute->getType();
            }
        }

        return $types;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeCodesByType($type)
    {
        return \array_values(\array_map(
            fn (AttributeInterface $attribute): string => $attribute->getCode(),
            \array_filter(
                $this->attributes->toArray(),
                fn (AttributeInterface $attribute): bool => $attribute->getType() === $type
            )
        ));
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
    public function getAttributesByGroups(array $groupsCode, int $limit, ?string $searchAfter)
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

    public function updateMainIdentifier(AttributeInterface $attribute): void
    {
        $attributesArray = $this->attributes->toArray();
        \array_walk($attributesArray, function (AttributeInterface $attribute) {
            $attribute->setIsMainIdentifier(false);
        });

        /** @var AttributeInterface $mainAttribute */
        $mainAttribute = $this->attributes->get($attribute->getCode());
        $mainAttribute->setIsMainIdentifier(true);
    }
}
