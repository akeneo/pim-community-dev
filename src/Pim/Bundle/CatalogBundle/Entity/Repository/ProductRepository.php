<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\AbstractQuery;
use Oro\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\VariantGroup;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends FlexibleEntityRepository
{
    /**
     * @param string $scope
     *
     * @return QueryBuilder
     */
    public function buildByScope($scope)
    {
        $qb = $this->findByWithAttributesQB();
        $qb
            ->andWhere(
                $qb->expr()->eq('Entity.enabled', '?1')
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('Value.scope', '?2'),
                    $qb->expr()->isNull('Value.scope')
                )
            )
            ->setParameter(1, true)
            ->setParameter(2, $scope);

        return $qb;
    }

    /**
     * @param Channel $channel
     *
     * @return QueryBuilder
     */
    public function buildByChannelAndCompleteness(Channel $channel)
    {
        $scope = $channel->getCode();
        $qb = $this->buildByScope($scope);
        $rootAlias = $qb->getRootAlias();
        $expression = $qb->expr()->eq('pCompleteness.ratio', '100').' AND '
            .$qb->expr()->eq('pCompleteness.channel', $channel->getId());
        $qb->innerJoin(
            $rootAlias .'.completenesses',
            'pCompleteness',
            'WITH',
            $expression
        );
        $treeId = $channel->getCategory()->getId();
        $expression = $qb->expr()->eq('pCategory.root', $treeId);
        $qb->innerJoin(
            $rootAlias.'.categories',
            'pCategory',
            'WITH',
            $expression
        );

        return $qb;
    }

    /**
     * Find products by existing family
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByExistingFamily()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where(
            $qb->expr()->isNotNull('p.family')
        );

        return $qb->getQuery()->getResult();
    }

    /**
     * @param array $ids
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function findByIds(array $ids)
    {
        $qb = $this->findByWithAttributesQB();
        $qb->andWhere(
            $qb->expr()->in('Entity.id', $ids)
        );

        return $qb->getQuery()->execute();
    }

    /**
     * @return integer[]
     */
    public function getAllIds()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('p.id')
            ->from($this->_entityName, 'p', 'p.id');

        return array_keys($qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY));
    }

    /**
     * Find all products in a variant group (by variant axis attribute values)
     *
     * @param VariantGroup $variantGroup
     * @param array        $criteria
     *
     * @return array
     */
    public function findAllForVariantGroup(VariantGroup $variantGroup, array $criteria = array())
    {
        $qb = $this->createQueryBuilder('Product');

        $qb
            ->where(
                $qb->expr()->eq('Product.variantGroup', '?1')
            )
            ->setParameter(1, $variantGroup);

        $i = 1;
        foreach ($criteria as $item) {
            $code = $item['attribute']->getCode();
            $qb
                ->innerJoin(
                    'Product.values',
                    sprintf('Value_%s', $code),
                    'WITH',
                    sprintf('Value_%s.attribute = ?%d AND Value_%s.option = ?%d', $code, ++$i, $code, ++$i)
                )
                ->setParameter($i - 1, $item['attribute'])
                ->setParameter($i, $item['option']);
        }

        return $qb->getQuery()->getResult();
    }
}
