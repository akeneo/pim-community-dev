<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeGroupRepository extends ReferableEntityRepository
{
    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function buildAllWithTranslations()
    {
        return $this->build()->addSelect('translation')->leftJoin('attribute_group.translations', 'translation');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildAllOrderedBySortOrder()
    {
        return $this->build()->orderBy('attribute_group.sortOrder');
    }

    /**
     * Find all ordered by label with fallback to default mecanism
     *
     * @return array
     */
    public function getIdToLabelOrderedBySortOrder()
    {
        $groups = $this->buildAllOrderedBySortOrder()->getQuery()->execute();
        $orderedGroups = array();
        foreach ($groups as $group) {
            $orderedGroups[$group->getId()] = $group->getLabel();
        }

        return $orderedGroups;
    }

    /**
     * Get the attribute group choices
     *
     * @return array
     */
    public function getAttributeGroupChoices()
    {
        $groups = $this->findAllWithTranslations();
        $choices = array();
        foreach ($groups as $group) {
            $choices[$group->getCode()] = $group->getLabel();
        }
        asort($choices);

        return $choices;
    }

    /**
     * Get the default attribute group
     *
     * @return null|AttributeGroup
     */
    public function findDefaultAttributeGroup()
    {
        return $this->findOneBy(array('code' => AttributeGroup::DEFAULT_GROUP_CODE));
    }

    /**
     * @param array $codes
     *
     * @return AttributeGroup[]
     */
    public function getAttributeGroupsFromAttributeCodes(array $codes)
    {
        $qb = $this->createQueryBuilder('ga');

        $query = $qb
            ->innerJoin('ga.attributes', 'a')
            ->where($qb->expr()->in('a.code', ':codes'))
            ->setParameter(':codes', $codes)
            ->getQuery();

        return $query->getResult();
    }

    /**
     * Find the largest attribute group sort order present in the database
     *
     * @return int
     */
    public function getMaxSortOrder()
    {
        return (int) $this->createQueryBuilder('ag')
            ->select('MAX(ag.sortOrder)')
            ->getQuery()
            ->execute([], AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }
}
