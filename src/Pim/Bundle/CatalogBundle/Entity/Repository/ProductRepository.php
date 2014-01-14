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
        $qb = $this->createQueryBuilder('p');

        $qb
            ->leftJoin('p.family', 'productFamily')
            ->leftJoin('productFamily.translations', 'ft', 'WITH', 'ft.locale = :dataLocale')
            ->leftJoin('p.groups', 'groups')
            ->leftJoin('groups.translations', 'gt', 'WITH', 'gt.locale = :dataLocale')
            ->leftJoin('p.values', 'values')
            ->leftJoin('values.options', 'valueOptions')
            ->leftJoin('values.prices', 'valuePrices')
            ->leftJoin('values.metric', 'valueMetrics')
            ->leftJoin('p.categories', 'category')
            ->leftJoin(
                'PimCatalogBundle:Locale',
                'locale',
                'WITH',
                'locale.code = :dataLocale'
            )
            ->leftJoin(
                'PimCatalogBundle:Channel',
                'channel',
                'WITH',
                'channel.code = :scopeCode'
            )
            ->leftJoin(
                'PimCatalogBundle:Completeness',
                'completeness',
                'WITH',
                'completeness.locale = locale.id AND completeness.channel = channel.id '.
                'AND completeness.productId = p.id'
            );

        $familyExpr = "(CASE WHEN ft.label IS NULL THEN productFamily.code ELSE ft.label END)";
        $qb
            ->addSelect(sprintf("%s AS familyLabel", $familyExpr))
            ->addSelect('values')
            ->addSelect('valuePrices')
            ->addSelect('valueOptions')
            ->addSelect('valueMetrics')
            ->addSelect('category')
            ->addSelect('groups')
            ->addSelect('completeness.ratio AS ratio');

        return $qb;
    }
}
