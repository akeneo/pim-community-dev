<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Repository\AttributeGroupRepositoryInterface;

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
    public function findDefaultAttributeGroup()
    {
        return $this->findOneBy(['code' => AttributeGroup::DEFAULT_GROUP_CODE]);
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

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier($code)
    {
        return $this->findOneBy(['code' => $code]);
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties()
    {
        return ['code'];
    }
}
