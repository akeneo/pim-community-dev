<?php

namespace Pim\Bundle\CatalogBundle\Entity\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Pim\Bundle\FlexibleEntityBundle\Entity\Repository\FlexibleEntityRepository;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Group;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;

/**
 * Product repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRepository extends FlexibleEntityRepository implements
    ProductRepositoryInterface,
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

        $qb->innerJoin(
            'Pim\Bundle\CatalogBundle\Model\Completeness',
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
    public function getProductCountByTree(ProductInterface $product)
    {
        $productMetadata = $this->getClassMetadata(get_class($product));

        $categoryAssoc = $productMetadata->getAssociationMapping('categories');

        $categoryClass = $categoryAssoc['targetEntity'];
        $categoryTable = $this->getEntityManager()->getClassMetadata($categoryClass)->getTableName();

        $categoryAssocTable = $categoryAssoc['joinTable']['name'];

        $sql = "SELECT".
               "    tree.id AS tree_id,".
               "    COUNT(category_product.product_id) AS product_count".
               "  FROM $categoryTable tree".
               "  JOIN $categoryTable category".
               "    ON category.root = tree.id".
               "  LEFT JOIN $categoryAssocTable category_product".
               "    ON category_product.product_id = :productId".
               "   AND category_product.category_id = category.id".
               " GROUP BY tree.id";

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->bindValue('productId', $product->getId());

        $stmt->execute();
        $productCounts = $stmt->fetchAll();
        $trees = array();
        foreach ($productCounts as $productCount) {
            $tree = array();
            $tree['productCount'] = $productCount['product_count'];
            $tree['tree'] = $this->getEntityManager()->getRepository($categoryClass)->find($productCount['tree_id']);
            $trees[] = $tree;
        }

        return $trees;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductsCountInCategory(
        CategoryInterface $category,
        QueryBuilder $categoryQb = null
    ) {
        $qb = $this->createQueryBuilder('p');
        $qb->select($qb->expr()->count('distinct p'));
        $qb->join('p.categories', 'node');

        if (null === $categoryQb) {
            $qb->where('node.id = :nodeId');
            $qb->setParameter('nodeId', $category->getId());
        } else {
            $qb->where($categoryQb->getDqlPart('where'));
            $qb->setParameters($categoryQb->getParameters());
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public function getProductIdsInCategory(
        CategoryInterface $category,
        QueryBuilder $categoryQb = null
    ) {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p.id');
        $qb->join('p.categories', 'node');

        if (null === $categoryQb) {
            $qb->where('node.id = :nodeId');
            $qb->setParameter('nodeId', $category->getId());
        } else {
            $qb->where($categoryQb->getDqlPart('where'));
            $qb->setParameters($categoryQb->getParameters());
        }

        $products = $qb->getQuery()->execute(array(), AbstractQuery::HYDRATE_ARRAY);

        $productIds = array();
        foreach ($products as $product) {
            $productIds[] = $product['id'];
        }
        $productIds = array_unique($productIds);

        return $productIds;
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
     * {@inheritdoc}
     */
    public function countProductsPerChannels()
    {
        $sql = <<<SQL
SELECT ch.label, COUNT(DISTINCT p.id) as total FROM pim_catalog_channel ch
    JOIN pim_catalog_category ca ON ca.root = ch.category_id
    JOIN %category_join_table% cp ON cp.category_id = ca.id
    JOIN %product_table% p ON p.id = cp.product_id
    WHERE p.is_enabled = 1
    GROUP BY ch.id, ch.label
SQL;

        $sql = $this->prepareDBALQuery($sql);
        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function countCompleteProductsPerChannels()
    {
        $sql = <<<SQL
    SELECT ch.label, lo.code as locale, COUNT(DISTINCT co.product_id) as total FROM pim_catalog_channel ch
    JOIN pim_catalog_category ca ON ca.root = ch.category_id
    JOIN %category_join_table% cp ON cp.category_id = ca.id
    JOIN %product_table% p ON p.id = cp.product_id
    JOIN pim_catalog_channel_locale cl ON cl.channel_id = ch.id
    JOIN pim_catalog_locale lo ON lo.id = cl.locale_id
    LEFT JOIN pim_catalog_completeness co
        ON co.locale_id = lo.id AND co.channel_id = ch.id AND co.product_id = p.id AND co.ratio = 100
    WHERE p.is_enabled = 1
    GROUP BY ch.id, lo.id, ch.label, lo.code
SQL;
        $sql = $this->prepareDBALQuery($sql);

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Replaces name of tables in DBAL queries
     *
     * @param string $sql
     *
     * @return string
     */
    protected function prepareDBALQuery($sql)
    {
        $categoryMapping = $this->getClassMetadata()->getAssociationMapping('categories');

        $valueMapping  = $this->getClassMetadata()->getAssociationMapping('values');
        $valueMetadata = $this->getEntityManager()->getClassMetadata($valueMapping['targetEntity']);

        $attributeMapping  = $valueMetadata->getAssociationMapping('attribute');

        return strtr(
            $sql,
            [
                '%category_join_table%' => $categoryMapping['joinTable']['name'],
                '%product_table%'       => $this->getClassMetadata()->getTableName(),
                '%product_value_table%' => $valueMetadata->getTableName(),
                '%attribute_table%'     => $valueMetadata->getTableName()
            ]
        );
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
     * @return QueryBuilder
     */
    public function createDatagridQueryBuilder()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id');

        $qb
            ->leftJoin('p.family', 'family')
            ->leftJoin('family.translations', 'ft', 'WITH', 'ft.locale = :dataLocale');

        $qb
            ->addSelect('p')
            ->addSelect('COALESCE(ft.label, CONCAT(\'[\', family.code, \']\')) as familyLabel');

        return $qb;
    }

    /**
     * @return QueryBuilder
     */
    public function createGroupDatagridQueryBuilder()
    {
        $qb = $this->_em->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id');

        $qb
            ->leftJoin('p.family', 'family')
            ->leftJoin('family.translations', 'ft', 'WITH', 'ft.locale = :dataLocale');

        $isCheckedExpr =
            'CASE WHEN ' .
            '(:currentGroup MEMBER OF p.groups '.
            'OR p.id IN (:data_in)) AND p.id NOT IN (:data_not_in)'.
            'THEN true ELSE false END';

        $inGroupExpr = 'CASE WHEN :currentGroup MEMBER OF p.groups THEN true ELSE false END';

        $qb
            ->addSelect('COALESCE(ft.label, CONCAT(\'[\', family.code, \']\')) as familyLabel')
            ->addSelect($isCheckedExpr.' AS is_checked')
            ->addSelect($inGroupExpr.' AS in_group');

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
        $qb = $this->_em->createQueryBuilder()
            ->select('p')
            ->from($this->_entityName, 'p', 'p.id');

        $qb
            ->leftJoin('p.family', 'family')
            ->leftJoin('family.translations', 'ft', 'WITH', 'ft.locale = :dataLocale')
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
            ->addSelect('COALESCE(ft.label, CONCAT(\'[\', family.code, \']\')) as familyLabel')
            ->addSelect($isCheckedExpr.' AS is_checked')
            ->addSelect($isAssociatedExpr.' AS is_associated');

        return $qb;
    }

    /**
     * Add completeness joins to query builder
     *
     * @param QueryBuilder $qb                the query builder
     * @param string       $completenessAlias the join alias
     */
    public function addCompleteness(QueryBuilder $qb, $completenessAlias)
    {
        $rootAlias         = $qb->getRootAlias();
        $localeAlias       = $completenessAlias.'Locale';
        $channelAlias      = $completenessAlias.'Channel';

        $qb
            ->leftJoin(
                'PimCatalogBundle:Locale',
                $localeAlias,
                'WITH',
                $localeAlias.'.code = :dataLocale'
            )
            ->leftJoin(
                'PimCatalogBundle:Channel',
                $channelAlias,
                'WITH',
                $channelAlias.'.code = :scopeCode'
            )
            ->leftJoin(
                'Pim\Bundle\CatalogBundle\Model\Completeness',
                $completenessAlias,
                'WITH',
                $completenessAlias.'.locale = '.$localeAlias.'.id AND '.
                $completenessAlias.'.channel = '.$channelAlias.'.id AND '.
                $completenessAlias.'.product = '.$rootAlias.'.id'
            );
    }

    /**
     * Returns true if a ProductValue with the provided value alread exists,
     * false otherwise.
     *
     * @param ProductValueInterface $value
     *
     * @return boolean
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
     * Find all common attributes ids linked to a family
     * A list of product ids can be passed as parameter
     *
     * @param array $productIds
     *
     * @return mixed
     */
    public function findFamilyCommonAttributeIds(array $productIds)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->select('a.id, COUNT(a.id) AS COUNT_ATT')
            ->innerJoin('p.family', 'f')
            ->innerJoin('f.attributes', 'a')
            ->groupBy('a.id');

        if (!empty($productIds)) {
            $qb->where($qb->expr()->in('p.id', $productIds));

            $subQb = $this->createQueryBuilder('p_sub');
            $subQb
                ->select($subQb->expr()->count('f_sub.id'))
                ->innerJoin('p_sub.family', 'f_sub')
                ->where($subQb->expr()->in('p_sub.id', $productIds));

            $qb->having('COUNT_ATT = ('. $subQb .')');
        }

        return $qb->getQuery()->execute();
    }

    /**
     * Find all common attribute ids with values from a list of product ids
     * Can't use ORM here because of QueryBuilder::from method which only take string
     * Only DBAL layer is used
     *
     * @param array $productIds
     *
     * @return mixed
     */
    public function findValuesCommonAttributeIds(array $productIds)
    {
        $sql = <<<SQL
    SELECT a.id, COUNT(a.id) AS COUNT_ATT
    FROM (
        SELECT a.id FROM %product_table% p
        INNER JOIN %product_value_table% pv ON pv.entity_id = p.id
        INNER JOIN %attribute_table% a ON a.id = pv.attribute_id
        WHERE p.id IN(%product_ids%)
        GROUP BY p.id, a.id) a
    GROUP BY a.id
    HAVING COUNT_ATT = (
        SELECT COUNT(p.id)
        FROM %product_table% p
        WHERE p.id IN(%product_ids%)
    )
SQL;

        $sql = strtr($sql, ['%product_ids%' => implode($productIds, ',')]);
        $sql = $this->prepareDBALQuery($sql);

        $stmt = $this->getEntityManager()->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
