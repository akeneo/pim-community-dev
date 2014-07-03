<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Pim\Bundle\CatalogBundle\Entity\Repository\AttributeRepository;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ReferableEntityRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends EntityRepository implements
    ProductRepositoryInterface,
    ReferableEntityRepositoryInterface
{
    /**
     * @var ProductQueryBuilder
     */
    protected $productQB;

    /**
     * @var AttributeRepository
     */
    protected $attributeRepository;

    /**
     * Set product query builder
     *
     * @param ProductQueryBuilder $productQB
     *
     * @return ProductRepositoryInterface
     */
    public function setProductQueryBuilder($productQB)
    {
        $this->productQB = $productQB;

        return $this;
    }

    /**
     * Set attribute repository
     *
     * @param AttributeRepository $attributeRepository
     *
     * @return ProductRepository
     */
    public function setAttributeRepository(AttributeRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function buildByScope($scope)
    {
        $qb = $this->findAllByAttributesQB();
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
            'pCompleteness.product = '.$rootAlias.' AND '.
            $qb->expr()->eq('pCompleteness.ratio', '100').' AND '.
            $qb->expr()->eq('pCompleteness.channel', $channel->getId());

        $rootEntity          = current($qb->getRootEntities());
        $completenessMapping = $this->_em->getClassMetadata($rootEntity)
            ->getAssociationMapping('completenesses');
        $completenessClass   = $completenessMapping['targetEntity'];
        $qb->innerJoin(
            $completenessClass,
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
        $qb = $this->findAllByAttributesQB();
        $qb->andWhere(
            $qb->expr()->in('Entity.id', $ids)
        );

        return $qb->getQuery()->execute();
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
    public function getFullProduct($id)
    {
        $qb = $this->getFullProductQB();

        return $qb
            ->where('p.id=:id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getFullProducts(array $productIds, array $attributeIds = array())
    {
        $qb = $this->getFullProductQB();
        $qb
            ->addSelect('c, assoc, g')
            ->leftJoin('v.attribute', 'a', $qb->expr()->in('a.id', $attributeIds))
            ->leftJoin('p.categories', 'c')
            ->leftJoin('p.associations', 'assoc')
            ->leftJoin('p.groups', 'g')
            ->where($qb->expr()->in('p.id', $productIds));

        return $qb->getQuery()->execute();
    }

    /**
     * Get full product query builder
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getFullProductQB()
    {
        return $this
            ->createQueryBuilder('p')
            ->select('p, f, v, pr, m, o, os')
            ->leftJoin('p.family', 'f')
            ->leftJoin('p.values', 'v')
            ->leftJoin('v.prices', 'pr')
            ->leftJoin('v.media', 'm')
            ->leftJoin('v.option', 'o')
            ->leftJoin('v.options', 'os');
    }

    /**
     * {@inheritdoc}
     */
    public function findByReference($code)
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->innerJoin('p.values', 'v')
            ->innerJoin('v.attribute', 'a')
            ->where('a.attributeType=:attribute_type')
            ->andWhere('v.varchar=:code')
            ->setParameter('attribute_type', 'pim_catalog_identifier')
            ->setParameter('code', $code)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getReferenceProperties()
    {
        return array($this->getAttributeRepository()->getIdentifierCode());
    }

    /**
     * Returns the ProductValue class
     *
     * @return string
     */
    protected function getValuesClass()
    {
        return $this->getClassMetadata()
            ->getAssociationTargetClass('values');
    }

    /**
     * Returns the Attribute class
     *
     * @return string
     */
    protected function getAttributeClass()
    {
        return $this->getEntityManager()
            ->getClassMetadata($this->getValuesClass())
            ->getAssociationTargetClass('attribute');
    }

    /**
     * Returns the Attribute
     *
     * @param string $code
     *
     * @return AbstractAttribute
     */
    protected function getAttributeByCode($code)
    {
        return $this->attributeRepository->findOneByCode($code);
    }

    /**
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id');

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createGroupDatagridQueryBuilder()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id');

        $isCheckedExpr =
            'CASE WHEN ' .
            '(:currentGroup MEMBER OF p.groups '.
            'OR p.id IN (:data_in)) AND p.id NOT IN (:data_not_in)'.
            'THEN true ELSE false END';
        $qb
            ->addSelect($isCheckedExpr.' AS is_checked');

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createVariantGroupDatagridQueryBuilder()
    {
        $qb = $this->createGroupDatagridQueryBuilder();
        $qb->andWhere($qb->expr()->in('p.id', ':productIds'));

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createAssociationDatagridQueryBuilder()
    {
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id');

        $qb
            ->leftJoin(
                'Pim\Bundle\CatalogBundle\Model\Association',
                'pa',
                'WITH',
                'pa.associationType = :associationType AND pa.owner = :product AND p MEMBER OF pa.products'
            );

        $qb->andWhere($qb->expr()->neq('p', ':product'));

        $isCheckedExpr =
            'CASE WHEN (pa IS NOT NULL OR p.id IN (:data_in)) AND p.id NOT IN (:data_not_in) ' .
            'THEN true ELSE false END';

        $isAssociatedExpr = 'CASE WHEN pa IS NOT NULL THEN true ELSE false END';

        $qb
            ->addSelect($isCheckedExpr.' AS is_checked')
            ->addSelect($isAssociatedExpr.' AS is_associated');

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function valueExists(ProductValueInterface $value)
    {
        $criteria = array(
            'attribute' => $value->getAttribute(),
            $value->getAttribute()->getBackendType() => $value->getData()
        );
        $result = $this->getEntityManager()->getRepository(get_class($value))->findBy($criteria);

        return (
            (0 !== count($result)) &&
            !(1 === count($result) && $value === ($result instanceof \Iterator ? $result->current() : current($result)))
        );
    }

    /**
     * @param integer $variantGroupId
     *
     * @return array product ids
     */
    public function getEligibleProductIdsForVariantGroup($variantGroupId)
    {
        $sql = 'SELECT count(ga.attribute_id) as nb '.
            'FROM pim_catalog_group_attribute as ga '.
            'WHERE ga.group_id = :groupId;';
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('groupId', $variantGroupId);
        $stmt->execute();
        $nbAxes = $stmt->fetch()['nb'];

        $sql = 'SELECT v.entity_id '.
            'FROM pim_catalog_group_attribute as ga '.
            "LEFT JOIN %product_value_table% as v ON v.attribute_id = ga.attribute_id ".
            'WHERE ga.group_id = :groupId '.
            'GROUP BY v.entity_id '.
            'having count(v.option_id) = :nbAxes ;';
        $sql = QueryBuilderUtility::prepareDBALQuery($this->_em, $this->_entityName, $sql);

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('groupId', $variantGroupId);
        $stmt->bindValue('nbAxes', $nbAxes);
        $stmt->execute();
        $results = $stmt->fetchAll();
        $productIds = array_map(
            function ($row) {
                return $row['entity_id'];
            },
            $results
        );

        return $productIds;
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByAttribute($qb, AbstractAttribute $attribute, $direction)
    {
        $this->getProductQueryBuilder($qb)->addAttributeSorter($attribute, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function applySorterByField($qb, $field, $direction)
    {
        $this->getProductQueryBuilder($qb)->addFieldSorter($field, $direction);
    }

    /**
     * {@inheritdoc}
     */
    public function findAllByAttributes(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        return $this
            ->findAllByAttributesQB($attributes, $criteria, $orderBy, $limit, $offset)
            ->getQuery()
            ->getResult();
    }

    /**
     * {@inheritdoc}
     */
    public function findOneBy(array $criteria)
    {
        $qb = $this->createQueryBuilder('p');
        $pqb = $this->getProductQueryBuilder($qb);
        foreach ($criteria as $field => $data) {
            if (is_array($data)) {
                $pqb->addAttributeFilter($data['attribute'], '=', $data['value']);
            } else {
                $pqb->addFieldFilter($field, '=', $data);
            }
        }

        $result = $qb->getQuery()->execute();

        if (count($result) > 1) {
            throw new \LogicException(
                sprintf(
                    'Many products have been found that match criteria:' . "\n" . '%s',
                    print_r($criteria, true)
                )
            );
        }

        return reset($result);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByWithValues($id)
    {
        $qb = $this->findAllByAttributesQB(array(), array('id' => $id));
        $qb->leftJoin('Attribute.translations', 'AttributeTranslations');
        $qb->leftJoin('Attribute.availableLocales', 'AttributeLocales');
        $qb->addSelect('Value');
        $qb->addSelect('Attribute');
        $qb->addSelect('AttributeTranslations');
        $qb->addSelect('AttributeLocales');
        $qb->leftJoin('Attribute.group', 'AttributeGroup');
        $qb->addSelect('AttributeGroup');
        $qb->leftJoin('AttributeGroup.translations', 'AGroupTranslations');
        $qb->addSelect('AGroupTranslations');

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * {@inheritdoc}
     *
     * @param QueryBuilder $qb
     *
     * @return ProductQueryBuilder
     */
    public function getProductQueryBuilder($qb)
    {
        if (!$this->productQB) {
            throw new \LogicException('Product query builder must be configured');
        }

        $this->productQB->setQueryBuilder($qb);

        return $this->productQB;
    }

    /**
     * Add join to values tables
     *
     * @param QueryBuilder $qb
     */
    protected function addJoinToValueTables(QueryBuilder $qb)
    {
        $qb->leftJoin(current($qb->getRootAliases()).'.values', 'Value')
            ->leftJoin('Value.attribute', 'Attribute')
            ->leftJoin('Value.options', 'ValueOption')
            ->leftJoin('ValueOption.optionValues', 'AttributeOptionValue');
    }

    /**
     * Finds entities and attributes values by a set of criteria, same coverage than findBy
     *
     * @param array      $attributes attribute codes
     * @param array      $criteria   criterias
     * @param array|null $orderBy    order by
     * @param int|null   $limit      limit
     * @param int|null   $offset     offset
     *
     * @return array The objects.
     */
    protected function findAllByAttributesQB(
        array $attributes = array(),
        array $criteria = null,
        array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        $qb = $this->createQueryBuilder('Entity');
        $this->addJoinToValueTables($qb);
        $productQb = $this->getProductQueryBuilder($qb);

        if (!is_null($criteria)) {
            foreach ($criteria as $attCode => $attValue) {
                $attribute = $this->getAttributeByCode($attCode);
                if ($attribute) {
                    $productQb->addAttributeFilter($attribute, '=', $attValue);
                } else {
                    $productQb->addFieldFilter($attCode, '=', $attValue);
                }
            }
        }
        if (!is_null($orderBy)) {
            foreach ($orderBy as $attCode => $direction) {
                $attribute = $this->getAttributeByCode($attCode);
                if ($attribute) {
                    $productQb->addAttributeSorter($attribute, $direction);
                } else {
                    $productQb->addFieldSorter($attCode, $direction);
                }
            }
        }

        // use doctrine paginator to avoid count problem with left join of values
        if (!is_null($offset) && !is_null($limit)) {
            $qb->setFirstResult($offset)->setMaxResults($limit);
            $paginator = new Paginator($qb->getQuery());

            return $paginator;
        }

        return $qb;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableAttributeIdsToExport(array $productIds)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('a.id')
            ->innerJoin('p.values', 'v')
            ->innerJoin('v.attribute', 'a')
            ->where($qb->expr()->in('p.id', $productIds))
            ->groupBy('a.id');

        $attributes = $qb->getQuery()->getArrayResult();
        $attributeIds = array();
        foreach ($attributes as $attribute) {
            $attributeIds[] = $attribute['id'];
        }

        return $attributeIds;
    }
}
