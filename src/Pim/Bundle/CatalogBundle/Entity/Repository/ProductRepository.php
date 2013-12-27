<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\AbstractQuery;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends FlexibleEntityRepository implements ProductRepositoryInterface,
    ReferableEntityRepositoryInterface
{
    /**
     * @var string
     */
    private $identifierCode;

    /**
     * {@inheritdoc}
     */
    public function buildByScope($scope)
    {
        $qb = $this->findByWithAttributesQB();
        $qb
            ->andWhere(
                $qb->expr()->eq('Entity.enabled', ':enabled')
            )
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq('Value.scope', ':scope'),
                    $qb->expr()->isNull('Value.scope')
                )
            )
            ->setParameter('enabled', true)
            ->setParameter('scope', $scope);

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function buildByChannelAndCompleteness(Channel $channel)
    {
        $scope = $channel->getCode();
        $qb = $this->buildByScope($scope);
        $rootAlias = $qb->getRootAlias();
        $expression =
            'pCompleteness.productId = '.$rootAlias.'.id AND '.
            $qb->expr()->eq('pCompleteness.ratio', '100').' AND '.
            $qb->expr()->eq('pCompleteness.channel', $channel->getId());

        $qb->innerJoin(
            'PimCatalogBundle:Completeness',
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getAllIds()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('p.id')
            ->from($this->_entityName, 'p', 'p.id');

        return array_keys($qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY));
    }

    /**
     * {@inheritdoc}
     */
    public function findAllForVariantGroup(Group $variantGroup, array $criteria = array())
    {
        $qb = $this->createQueryBuilder('Product');

        $qb
            ->where(':variantGroup MEMBER OF Product.groups')
            ->setParameter('variantGroup', $variantGroup);

        $index = 0;
        foreach ($criteria as $item) {
            $code = $item['attribute']->getCode();
            $qb
                ->innerJoin(
                    'Product.values',
                    sprintf('Value_%s', $code),
                    'WITH',
                    sprintf('Value_%s.attribute = ?%d AND Value_%s.option = ?%d', $code, ++$index, $code, ++$index)
                )
                ->setParameter($index - 1, $item['attribute'])
                ->setParameter($index, $item['option']);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByMissingCompleteness(Channel $channel)
    {
        return $this
            ->findByWithAttributesQB()
            ->andWhere(
                'Entity.id NOT IN (
                    SELECT p.id FROM Pim\Bundle\CatalogBundle\Entity\Product p
                    LEFT JOIN p.completenesses c
                    LEFT JOIN c.channel ch
                    WHERE ch.id = :channel
                )'
            )
            ->setParameter('channel', $channel->getId())
            ->getQuery()
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProduct($id)
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p, f, v, pr, m, o, os')
            ->leftJoin('p.family', 'f')
            ->leftJoin('p.values', 'v')
            ->leftJoin('v.prices', 'pr')
            ->leftJoin('v.media', 'm')
            ->leftJoin('v.option', 'o')
            ->leftJoin('v.options', 'os')
            ->where('p.id=:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->findByWithAttributes(array(), array($this->getIdentifierCode() => $code));
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array($this->getIdentifierCode());
    }

    /**
     * Returns the identifier code
     *
     * @return string
     */
    public function getIdentifierCode()
    {
        if (!isset($this->identifierCode)) {
            $this->identifierCode = $this->getEntityManager()
                ->createQuery(
                    sprintf(
                        'SELECT a.code FROM %s a WHERE a.attributeType=:identifier_type ',
                        $this->getAttributeClass()
                    )
                )
                ->setParameter('identifier_type', 'pim_catalog_identifier')
                ->getSingleScalarResult();
        }

        return $this->identifierCode;
    }

    /**
     * Returns the ProductValue class
     *
     * @return string
     */
    protected function getValuesClass()
    {
        return $this->getClassMetadata()->getAssociationTargetClass('values');
    }

    /**
     * Returns the ProductAttribute class
     *
     * @return string
     */
    protected function getAttributeClass()
    {
        return $this->getEntityManager()
            ->getClassMetadata($this->getValuesClass())
            ->getAssociationTargetClass('attributes');
    }
}
