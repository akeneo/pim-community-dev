<?php

namespace Pim\Bundle\DataGridBundle\Datasource;

use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Pim\Bundle\DataGridBundle\Extension\Pager\PagerExtension;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Query\Filter\Operators;
use Pim\Component\Catalog\Query\ProductQueryBuilderInterface;
use Pim\Component\Catalog\Query\Sorter\Directions;

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
        $currentProduct = $this->getConfiguration('current_product', false);
        if (!$currentProduct instanceof ProductInterface) {
            throw InvalidObjectException::objectExpected($currentProduct, ProductInterface::class);
        }

        $associatedProductsIdentifiers = $this->getAssociatedProductsIdentifiers(
            $currentProduct,
            $this->getConfiguration('association_type_id')
        );

        $limit = (int)$this->getConfiguration(PagerExtension::PER_PAGE_PARAM, false);
        $locale = $this->getConfiguration('locale_code');
        $scope = $this->getConfiguration('scope_code');
        $from = null !== $this->getConfiguration('from', false) ?
            (int)$this->getConfiguration('from', false) : 0;

        $productCursor = $this->pqb->execute();

        $associatedProducts = $this->getAssociatedProducts(
            $associatedProductsIdentifiers,
            $limit,
            0,
            $locale,
            $scope
        );

        $rows = ['totalRecords' => count($associatedProducts)];
        $rows['data'] = $associatedProducts;

        return $rows;
    }

    /**
     * @param ProductInterface $product
     * @param string           $associationTypeId
     *
     * @return string[]
     */
    protected function getAssociatedProductsIdentifiers(ProductInterface $product, $associationTypeId)
    {
        $identifiers = [];

        foreach ($product->getAssociations() as $association) {
            if ($association->getAssociationType()->getId() === (int)$associationTypeId) {
                foreach ($association->getProducts() as $associatedProduct) {
                    $identifiers[] = $associatedProduct->getIdentifier();
                }
            }
        }

        return $identifiers;
    }

    /**
     * @param array  $associatedProductsIdentifiers
     * @param int    $limit
     * @param int    $from
     * @param string $locale
     * @param string $scope
     *
     * @return array
     */
    protected function getAssociatedProducts(
        array $associatedProductsIdentifiers,
        $limit,
        $from,
        $locale,
        $scope
    ) {
        $pqb = $this->createQueryBuilder($limit, $from, $locale, $scope);
        $pqb->addFilter('identifier', Operators::IN_LIST, $associatedProductsIdentifiers);

        $products = $pqb->execute();

        return $this->normalizeProducts($products, $associatedProductsIdentifiers, $locale, $scope);
    }

    /**
     * @param CursorInterface $products
     * @param string[]        $associatedProductsIdentifiers
     * @param string          $locale
     * @param string          $scope
     *
     * @return array
     */
    protected function normalizeProducts(
        CursorInterface $products,
        array $associatedProductsIdentifiers,
        $locale,
        $scope
    ) {
        $dataLocale = $this->getParameters()['dataLocale'];

        $context = [
            'locales'     => [$locale],
            'channels'    => [$scope],
            'data_locale' => $dataLocale,
        ];

        $data = [];
        foreach ($products as $product) {
            $context['is_associated'] = in_array($product->getIdentifier(), $associatedProductsIdentifiers);

            $normalizedProduct = array_merge(
                $this->normalizer->normalize($product, 'datagrid', $context),
                [
                    'id'         => $product->getId(),
                    'image'      => $product->getImage(),
                    'dataLocale' => $dataLocale,
                ]
            );

            $data[] = new ResultRecord($normalizedProduct);
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
}
