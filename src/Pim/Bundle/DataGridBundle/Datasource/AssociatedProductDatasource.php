<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Extension\Pager\PagerExtension;
use Pim\Bundle\DataGridBundle\Normalizer\IdEncoder;
use Pim\Component\Catalog\Model\AssociationInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;

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

        $associatedProducts = $this->getAssociatedProducts(
            $associatedProductsIds,
            $limit,
            $from,
            $locale,
            $scope
        );

        $productModelLimit = $limit - count($associatedProducts);
        $associatedProductModels = [];
        if ($productModelLimit > 0) {
            $productModelFrom = $from - count($associatedProductsIds) + count($associatedProducts);
            $associatedProductModels = $this->getAssociatedProductModels(
                $associatedProductModelsIds,
                $productModelLimit,
                max($productModelFrom, 0),
                $locale,
                $scope
            );
        }

        $rows = ['totalRecords' => count($associatedProductsIds) + count($associatedProductModelsIds)];
        $rows['data'] = array_merge($associatedProducts, $associatedProductModels);

        return $rows;
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
     * @return array
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
        $products = $pqb->execute();

        return $this->normalizeProductsAndProductModels($products, $locale, $scope);
    }

    /**
     * @param array  $associatedProductModelsIds
     * @param int    $limit
     * @param int    $from
     * @param string $locale
     * @param string $scope
     *
     * @return array
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
        $products = $pqb->execute();

        return $this->normalizeProductsAndProductModels($products, $locale, $scope);
    }

    /**
     * @param CursorInterface $products
     * @param string          $locale
     * @param string          $scope
     *
     * @return array
     */
    protected function normalizeProductsAndProductModels(
        CursorInterface $products,
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
     * @return null|AssociationInterface
     */
    private function getAssociation(ProductInterface $sourceProduct, $associationTypeId): ?AssociationInterface
    {
        foreach ($sourceProduct->getAssociations() as $association) {
            if ($association->getAssociationType()->getId() === (int)$associationTypeId) {
                return $association;
            }
        }

        return null;
    }
}
