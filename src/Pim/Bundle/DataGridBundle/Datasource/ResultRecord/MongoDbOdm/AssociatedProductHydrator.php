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
        $associationTypeId = (int)$options['association_type_id'];
        $currentProduct    = $options['current_product'];

        $query           = $queryBuilder->hydrate(false)->getQuery();
        $queryDefinition = $query->getQuery();

        $hasCurrentProduct    = null !== $currentProduct;
        $sortedByIsAssociated = isset($queryDefinition['sort']['normalizedData.is_associated']);
        $hasResults           = 0 !== $queryDefinition['limit'];

        if ($hasCurrentProduct && $sortedByIsAssociated && $hasResults) {
            $associatedIds     = $this->getAssociatedProductIds($currentProduct, $associationTypeId);
            $nbTotalAssociated = count($associatedIds);

            $limit    = $queryDefinition['limit'];
            $skip     = $queryDefinition['skip'];
            $rawQuery = $queryDefinition['query'];

            $queryDefinition['skip'];
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
                $skip = ($skip > 0) ? $skip : 0;

                $nonAssociatedProducts = $this->getNonAssociatedProducts(
                    $queryBuilder,
                    $associatedIds,
                    $rawQuery,
                    $limit,
                    $skip
                );
            }

            $results = $associatedProducts + $nonAssociatedProducts;
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
     * @param Builder  $queryBuilder
     * @param string[] $associatedIds
     * @param array    $rawQuery
     * @param int      $limit
     * @param int      $skip
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
     * @param Builder  $queryBuilder
     * @param string[] $associatedIds
     * @param array    $rawQuery
     * @param int      $limit
     * @param int      $skip
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
     * @param Builder $queryBuilder
     * @param array   $rawQuery
     * @param int     $limit
     * @param int     $skip
     * @param bool    $isAssociated
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     * @return array
     */
    protected function getProductsAsArray(
        Builder $queryBuilder,
        array $rawQuery,
        $limit,
        $skip,
        $isAssociated
    ) {
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
}
