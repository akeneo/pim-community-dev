<?php

namespace Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm;

use Doctrine\ODM\MongoDB\Query\Builder;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\HydratorInterface;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\AssociationTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\CompletenessTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FamilyTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\FieldsTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\GroupsTransformer;
use Pim\Bundle\DataGridBundle\Datasource\ResultRecord\MongoDbOdm\Product\ValuesTransformer;

/**
 * Hydrate results of Doctrine MongoDB query as ResultRecord array
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssociatedProductHydrator implements HydratorInterface
{
    /** @var string */
    protected $productClass;

    /**
     * @param ProductInterface $productClass
     */
    public function __construct($productClass)
    {
        $this->productClass = $productClass;
    }

    /**
     * {@inheritdoc}
     */
    public function hydrate($queryBuilder, array $options = [])
    {
        $locale            = $options['locale_code'];
        $scope             = $options['scope_code'];
        $config            = $options['attributes_configuration'];
        $groupId           = $options['current_group_id'];
        $associationTypeId = (int) $options['association_type_id'];
        $currentProduct    = $options['current_product'];

        $query           = $queryBuilder->hydrate(false)->getQuery();
        $queryDefinition = $query->getQuery();

        $hasCurrentProduct    = null !== $currentProduct;
        $sortedByIsAssociated = isset($queryDefinition['sort']['normalizedData.is_associated']);
        $hasResults           = 0 !== $queryDefinition['limit'];

        if ($hasCurrentProduct && $sortedByIsAssociated && $hasResults) {
            $associatedIds = $this->getAssociatedProductIds($currentProduct, $associationTypeId);

            $limit    = $queryDefinition['limit'];
            $skip     = $queryDefinition['skip'];
            $rawQuery = $queryDefinition['query'];

            if (-1 === (int) $queryDefinition['sort']['normalizedData.is_associated']) {
                $results = $this->getProductsSortedByIsAssociatedDesc(
                    $queryBuilder,
                    $associatedIds,
                    $rawQuery,
                    $limit,
                    $skip,
                    count($associatedIds)
                );
            } else {
                $results = $this->getProductsSortedByIsAssociatedAsc(
                    $queryBuilder,
                    $associatedIds,
                    $rawQuery,
                    $limit,
                    $skip,
                    $this->countProducts($queryBuilder, $associatedIds, $rawQuery)
                );
            }
        } else {
            $results = $query->execute();
        }

        $attributes = [];
        foreach ($config as $attributeConf) {
            $attributes[$attributeConf['id']] = $attributeConf;
        }

        $rows              = [];
        $fieldsTransformer = new FieldsTransformer();
        $valuesTransformer = new ValuesTransformer();
        $familyTransformer = new FamilyTransformer();
        $complTransformer  = new CompletenessTransformer();
        $groupsTransformer = new GroupsTransformer();
        $assocTransformer  = new AssociationTransformer();

        foreach ($results as $result) {
            $result = $fieldsTransformer->transform($result, $locale);
            $result = $valuesTransformer->transform($result, $attributes, $locale, $scope);
            $result = $familyTransformer->transform($result, $locale);
            $result = $complTransformer->transform($result, $locale, $scope);
            $result = $groupsTransformer->transform($result, $locale, $groupId);
            $result = $assocTransformer->transform($result, $associationTypeId, $currentProduct);

            $result['is_checked'] = $result['is_associated'];

            $rows[] = new ResultRecord($result);
        }

        return $rows;
    }

    /**
     * Get products sorted by associated_id desc (is associated first)
     *
     * @param Builder $queryBuilder      the query builder
     * @param array   $associatedIds     the ids of the products that are associated
     * @param array   $rawQuery          the query parameters
     * @param int     $limit             the limit of products to select for the page
     * @param int     $skip              the number of products to skip for the page
     * @param int     $nbTotalAssociated the number total of associated products
     *
     * @return array
     */
    protected function getProductsSortedByIsAssociatedDesc(
        Builder $queryBuilder,
        array $associatedIds,
        array $rawQuery,
        $limit,
        $skip,
        $nbTotalAssociated
    ) {
        $associatedProducts = $this->getAssociatedProducts(
            $queryBuilder,
            $associatedIds,
            $rawQuery,
            $limit,
            $skip
        );

        $nonAssociatedProducts = [];
        $nbAssociated = count($associatedProducts);
        if ($limit > $nbAssociated) {
            $limit -= $nbAssociated;
            $skip -= $nbTotalAssociated;
            $skip = max($skip, 0);

            $nonAssociatedProducts = $this->getNonAssociatedProducts(
                $queryBuilder,
                $associatedIds,
                $rawQuery,
                $limit,
                $skip
            );
        }

        return $associatedProducts + $nonAssociatedProducts;
    }

    /**
     * Get products sorted by associated_id asc (is associated last)
     *
     * @param Builder $queryBuilder         the query builder
     * @param array   $associatedIds        the ids of the products that are associated
     * @param array   $rawQuery             the query parameters
     * @param int     $limit                the limit of products to select for the page
     * @param int     $skip                 the number of products to skip for the page
     * @param int     $nbTotalNonAssociated the number total of non associated products
     *
     * @return array
     */
    protected function getProductsSortedByIsAssociatedAsc(
        Builder $queryBuilder,
        array $associatedIds,
        array $rawQuery,
        $limit,
        $skip,
        $nbTotalNonAssociated
    ) {
        $nonAssociatedProducts = $this->getNonAssociatedProducts(
            $queryBuilder,
            $associatedIds,
            $rawQuery,
            $limit,
            $skip
        );

        $associatedProducts = [];
        $nbNonAssociated = count($nonAssociatedProducts);
        if ($limit > $nbNonAssociated) {
            $limit -= $nbNonAssociated;
            $skip -= $nbTotalNonAssociated;
            $skip = max($skip, 0);

            $associatedProducts = $this->getAssociatedProducts(
                $queryBuilder,
                $associatedIds,
                $rawQuery,
                $limit,
                $skip
            );
        }

        return $nonAssociatedProducts + $associatedProducts;
    }

    /**
     * Get Mongo Ids of the associated products of the current product
     *
     * @param ProductInterface $product
     * @param int              $associationTypeId
     *
     * @return \MongoId[]
     */
    protected function getAssociatedProductIds(ProductInterface $product, $associationTypeId)
    {
        $ids = [];
        foreach ($product->getAssociations() as $association) {
            if ($association->getAssociationType()->getId() !== $associationTypeId) {
                continue;
            }
            foreach ($association->getProducts() as $associatedProduct) {
                $ids[] = new \MongoId($associatedProduct->getId());
            }
        }

        return $ids;
    }

    /**
     * Get the associated products for the current page
     *
     * @param Builder  $queryBuilder  the query builder
     * @param string[] $associatedIds the ids of the products that are associated
     * @param array    $rawQuery      the query parameters
     * @param int      $limit         the limit of products to select for the page
     * @param int      $skip          the number of products to skip for the page
     *
     * @return array
     */
    protected function getAssociatedProducts(
        Builder $queryBuilder,
        array $associatedIds,
        array $rawQuery,
        $limit,
        $skip
    ) {
        $in = ['_id' => ['$in' => $associatedIds]];
        $rawQuery['$and'][] = $in;

        return $this->getProductsAsArray($queryBuilder, $rawQuery, $limit, $skip, true);
    }

    /**
     * Get the non associated products for the current page
     *
     * @param Builder  $queryBuilder  the query builder
     * @param string[] $associatedIds the ids of the products that are associated
     * @param array    $rawQuery      the query parameters
     * @param int      $limit         the limit of products to select for the page
     * @param int      $skip          the number of products to skip for the page
     *
     * @return array
     */
    protected function getNonAssociatedProducts(
        Builder $queryBuilder,
        array $associatedIds,
        array $rawQuery,
        $limit,
        $skip
    ) {
        $nin = ['_id' => ['$nin' => $associatedIds]];
        $rawQuery['$and'][] = $nin;

        return $this->getProductsAsArray($queryBuilder, $rawQuery, $limit, $skip, false);
    }

    /**
     * Get products as array according to the query, the limit and the skip. The column "is_associated"
     * is also added.
     *
     * @param Builder $queryBuilder the query builder
     * @param array   $rawQuery     the ids of the products that are associated
     * @param int     $limit        the query parameters
     * @param int     $skip         the limit of products to select for the page
     * @param bool    $isAssociated value of the "is associated" column that will be added
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @return array
     */
    protected function getProductsAsArray(Builder $queryBuilder, array $rawQuery, $limit, $skip, $isAssociated)
    {
        $queryBuilder->find();
        $queryBuilder->setQueryArray($rawQuery);
        $queryBuilder->limit($limit);
        $queryBuilder->skip($skip);

        $query   = $queryBuilder->getQuery();
        $results = $query->execute()->toArray();
        foreach ($results as &$product) {
            $product['is_associated'] = $isAssociated;
        }

        return $results;
    }

    /**
     * Get the number of products according to the query
     *
     * @param Builder $queryBuilder  the query builder
     * @param array   $associatedIds the ids of the products that are associated
     * @param array   $rawQuery      the query parameters
     *
     * @return int
     */
    protected function countProducts(Builder $queryBuilder, array $associatedIds, array $rawQuery)
    {
        $queryBuilder->count();
        $queryBuilder->setQueryArray($rawQuery);
        $queryBuilder->limit(0);
        $queryBuilder->skip(0);

        $count = $queryBuilder->getQuery()->execute() - count($associatedIds);

        return max($count, 0);
    }
}
