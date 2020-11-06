<?php

namespace Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Repository\AttributeGroupRepositoryInterface;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupRepository extends EntityRepository implements AttributeGroupRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function getIdToLabelOrderedBySortOrder(): array
    {
        $groups = $this->buildAllOrderedBySortOrder()->getQuery()->execute();
        $orderedGroups = [];
        foreach ($groups as $group) {
            $orderedGroups[$group->getId()] = $group->getLabel();
        }

        return $orderedGroups;
    }

    /**
     * {@inheritdoc}
     */
    public function findDefaultAttributeGroup(): ?AttributeGroupInterface
    {
        return $this->findOneBy(['code' => AttributeGroup::DEFAULT_GROUP_CODE]);
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxSortOrder(): int
    {
        return (int) $this->createQueryBuilder('ag')
            ->select('MAX(ag.sortOrder)')
            ->getQuery()
            ->execute([], AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier(string $code): ?object
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return ['code'];
    }

    protected function buildAllOrderedBySortOrder(): QueryBuilder
    {
        return $this->createQueryBuilder('attribute_group')
            ->orderBy('attribute_group.sortOrder');
    }
}
