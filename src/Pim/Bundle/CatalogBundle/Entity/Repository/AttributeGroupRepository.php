<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\CatalogBundle\Doctrine\ReferableEntityRepository;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;

/**
 * Repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated will be moved to Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository in 1.4
 */
class AttributeGroupRepository extends ReferableEntityRepository implements AttributeGroupRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findAllWithTranslations()
    {
        $qb = $this->createQueryBuilder('attribute_group')
            ->addSelect('translation')
            ->leftJoin('attribute_group.translations', 'translation');

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function findDefaultAttributeGroup()
    {
        return $this->findOneBy(array('code' => AttributeGroup::DEFAULT_GROUP_CODE));
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getMaxSortOrder()
    {
        return (int) $this->createQueryBuilder('ag')
            ->select('MAX(ag.sortOrder)')
            ->getQuery()
            ->execute([], AbstractQuery::HYDRATE_SINGLE_SCALAR);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildAllOrderedBySortOrder()
    {
        return $this->createQueryBuilder('attribute_group')
            ->orderBy('attribute_group.sortOrder');
    }
}
