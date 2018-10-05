<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;
use Oro\Bundle\PimDataGridBundle\Normalizer\IdEncoder;

/**
 * Product datasource dedicated to the product association datagrid.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociatedProductDatasource extends ProductDatasource
{
    /** @var string */
    protected $sortOrder;

    /**
     * Sets the sort order passed to the "is associated" datagrid sorter.
     *
     * @param string $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * Returns products normalized for the datasource.
     *
     * The associated products and the non associated products are fetched and
     * normalized separately, but returned as one array:
     * - if "$sortOrder" is descending, associated products are fetched first.
     * - if it is ascending, then non associated products are fetched first.
     * - finally, if no order on "is_associated" field is specified, they are
     *   fetched ordered by IDs.
     *
     * Edited product is never fetch, as there is no sense to associate a product
     * to itself.
     *
     * {@inheritdoc}
     */
    public function getResults()
    {
        $sourceProduct = $this->getConfiguration('current_product', false);
        if (!$sourceProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected($sourceProduct, ProductInterface::class);
        }

        $association = $this->getAssociation($sourceProduct, $this->getConfiguration('association_type_id'));
        if (null === $association) {
            return ['totalRecords' => 0, 'data' => []];
        }

        $associatedProductsIds = $this->getAssociatedProductIds($association);
        $associatedProductModelsIds = $this->getAssociatedProductModelIds($association);

        $limit = (int)$this->getConfiguration(PagerExtension::PER_PAGE_PARAM, false);
        $locale = $this->getConfiguration('locale_code');
        $scope = $this->getConfiguration('scope_code');
        $from = null !== $this->getConfiguration('from', false) ?
            (int) $this->getConfiguration('from', false) : 0;

        $associatedProductsIdsFromParent = [];
        $associatedProductModelsIdsFromParent = [];
        $parentAssociation = $this->getParentAssociation($sourceProduct, $this->getConfiguration('association_type_id'));
        if (null !== $parentAssociation) {
            $associatedProductsIdsFromParent = $this->getAssociatedProductIds($parentAssociation);
            $associatedProductModelsIdsFromParent = $this->getAssociatedProductModelIds($parentAssociation);
        }

        $associatedProducts = $this->getAssociatedProducts(
            $associatedProductsIds,
            $limit,
            $from,
            $locale,
            $scope
        );

        $normalizedAssociatedProducts = $this->normalizeProductsAndProductModels(
            $associatedProducts,
            $associatedProductsIdsFromParent,
            $locale,
            $scope
        );

        $productModelLimit = $limit - $associatedProducts->count();
        $normalizedAssociatedProductModels = [];
        if ($productModelLimit > 0) {
            $productModelFrom = $from - count($associatedProductsIds) + $associatedProducts->count();
            $associatedProductModels = $this->getAssociatedProductModels(
                $associatedProductModelsIds,
                $productModelLimit,
                max($productModelFrom, 0),
                $locale,
                $scope
            );

            $normalizedAssociatedProductModels = $this->normalizeProductsAndProductModels(
                $associatedProductModels,
                $associatedProductModelsIdsFromParent,
                $locale,
                $scope
            );
        }

        $rows = ['totalRecords' => count($associatedProductsIds) + count($associatedProductModelsIds)];
        $rows['data'] = array_merge($normalizedAssociatedProducts, $normalizedAssociatedProductModels);

        return $rows;
    }

    /**
     * @param EntityWithFamilyVariantInterface $product
     * @param mixed                            $associationTypeId
     *
     * @return AssociationInterface|null
     */
    protected function getParentAssociation(EntityWithFamilyVariantInterface $product, $associationTypeId): ?AssociationInterface
    {
        $parent = $product->getParent();

        if (null === $parent) {
            return null;
        }

        foreach ($parent->getAllAssociations() as $association) {
            if ($association->getAssociationType()->getId() === (int)$associationTypeId) {
                return $association;
            }
        }

        return null;
    }

    /**
     * @param AssociationInterface $association
     *
     * @return string[]
     */
    protected function getAssociatedProductIds(AssociationInterface $association): array
    {
        $ids = [];
        foreach ($association->getProducts() as $associatedProduct) {
            $ids[] = IdEncoder::encode(IdEncoder::PRODUCT_TYPE, $associatedProduct->getId());
        }

        return $ids;
    }

    /**
     * @param AssociationInterface $association
     *
     * @return string[]
     */
    protected function getAssociatedProductModelIds(AssociationInterface $association): array
    {
        $ids = [];
        foreach ($association->getProductModels() as $associatedProduct) {
            $ids[] = IdEncoder::encode(IdEncoder::PRODUCT_MODEL_TYPE, $associatedProduct->getId());
        }

        return $ids;
    }

    /**
     * @param array  $associatedProductsIds
     * @param int    $limit
     * @param int    $from
     * @param string $locale
     * @param string $scope
     *
     * @return CursorInterface
     */
    protected function getAssociatedProducts(
        array $associatedProductsIds,
        $limit,
        $from,
        $locale,
        $scope
    ) {
        $pqb = $this->createQueryBuilder($limit, $from, $locale, $scope);
        $pqb->addFilter('id', Operators::IN_LIST, $associatedProductsIds);
        $pqb->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);

        return $pqb->execute();
    }

    /**
     * @param array  $associatedProductModelsIds
     * @param int    $limit
     * @param int    $from
     * @param string $locale
     * @param string $scope
     *
     * @return CursorInterface
     */
    protected function getAssociatedProductModels(
        array $associatedProductModelsIds,
        $limit,
        $from,
        $locale,
        $scope
    ) {
        $pqb = $this->createQueryBuilder($limit, $from, $locale, $scope);
        $pqb->addFilter('id', Operators::IN_LIST, $associatedProductModelsIds);
        $pqb->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class);

        return $pqb->execute();
    }

    /**
     * @param CursorInterface $products
     * @param array           $idsFromInheritance
     * @param string          $locale
     * @param string          $scope
     *
     * @return array
     */
    protected function normalizeProductsAndProductModels(
        CursorInterface $products,
        array $identifiersFromInheritance,
        $locale,
        $scope
    ) {
        $dataLocale = $this->getParameters()['dataLocale'];

        $context = [
            'locales'       => [$locale],
            'channels'      => [$scope],
            'data_locale'   => $dataLocale,
            'is_associated' => true,
        ];

        $data = [];
        foreach ($products as $product) {
            $normalized = array_merge(
                $this->normalizer->normalize($product, 'datagrid', $context),
                [
                    'id'         => sprintf(
                        '%s-%s',
                        $product instanceof ProductModelInterface ? 'product-model' : 'product',
                        $product->getId()
                    ),
                    'dataLocale' => $dataLocale,
                    'is_associated' => true,
                ]
            );

            if ($product instanceof ProductModelInterface) {
                $identifier = IdEncoder::encode(IdEncoder::PRODUCT_MODEL_TYPE, $product->getId());
            } else {
                $identifier = IdEncoder::encode(IdEncoder::PRODUCT_TYPE, $product->getId());
            }

            $normalized['from_inheritance'] = in_array($identifier, $identifiersFromInheritance);

            $data[] = new ResultRecord($normalized);
        }

        return $data;
    }

    /**
     * Creates a product query builder.
     *
     * As associated products and non associated products are fetched separately,
     * and that "search_after" parameter can be changed according to pagination,
     * we need to create two PQBs with different settings.
     *
     * @param int    $limit
     * @param int    $from
     * @param string $locale
     * @param string $scope
     *
     * @return ProductQueryBuilderInterface
     */
    protected function createQueryBuilder($limit, $from, $locale, $scope)
    {
        if (null === $repositoryParameters = $this->getConfiguration('repository_parameters', false)) {
            $repositoryParameters = [];
        }

        if (null === $method = $this->getConfiguration('repository_method', false)) {
            $method = 'createQueryBuilder';
        }

        $factoryConfig['repository_parameters'] = $repositoryParameters;
        $factoryConfig['repository_method'] = $method;
        $factoryConfig['limit'] = $limit;
        $factoryConfig['from'] = $from;
        $factoryConfig['default_locale'] = $locale;
        $factoryConfig['default_scope'] = $scope;
        $factoryConfig['filters'] = $this->pqb->getRawFilters();

        $pqb = $this->factory->create($factoryConfig);

        return $pqb;
    }

    /**
     * @param ProductInterface           $sourceProduct
     * @param mixed                      $associationTypeId
     *
     * @return null|AssociationInterface
     */
    private function getAssociation(ProductInterface $sourceProduct, $associationTypeId): ?AssociationInterface
    {
        foreach ($sourceProduct->getAllAssociations() as $association) {
            if ($association->getAssociationType()->getId() === (int)$associationTypeId) {
                return $association;
            }
        }

        return null;
    }
}
