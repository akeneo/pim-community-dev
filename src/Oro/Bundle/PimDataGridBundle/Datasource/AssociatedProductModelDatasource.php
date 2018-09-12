<?php

namespace Oro\Bundle\PimDataGridBundle\Datasource;

use Akeneo\Pim\Enrichment\Component\Product\Model\AssociationInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\Filter\Operators;
use Akeneo\Pim\Enrichment\Component\Product\Query\ProductQueryBuilderInterface;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecord;
use Oro\Bundle\PimDataGridBundle\Extension\Pager\PagerExtension;

/**
 * Product datasource dedicated to the product association datagrid.
 *
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssociatedProductModelDatasource extends ProductDatasource
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
        if (!$sourceProduct instanceof ProductModelInterface) {
            throw InvalidObjectException::objectExpected($sourceProduct, ProductModelInterface::class);
        }

        $association = $this->getAssociation($sourceProduct, $this->getConfiguration('association_type_id'));
        if (null === $association) {
            return ['totalRecords' => 0, 'data' => []];
        }

        $associatedProductsIdentifiers = $this->getAssociatedProductIdentifiers($association);
        $associatedProductModelsIdentifiers = $this->getAssociatedProductModelIdentifiers($association);

        $limit = (int)$this->getConfiguration(PagerExtension::PER_PAGE_PARAM, false);
        $locale = $this->getConfiguration('locale_code');
        $scope = $this->getConfiguration('scope_code');
        $from = null !== $this->getConfiguration('from', false) ?
            (int) $this->getConfiguration('from', false) : 0;

        $associatedProductsIdentifiersFromParent = [];
        $associatedProductModelsIdentifiersFromParent = [];
        $parentAssociation = $this->getParentAssociation($sourceProduct, $this->getConfiguration('association_type_id'));
        if (null !== $parentAssociation) {
            $associatedProductsIdentifiersFromParent = $this->getAssociatedProductIdentifiers($parentAssociation);
            $associatedProductModelsIdentifiersFromParent = $this->getAssociatedProductModelIdentifiers($parentAssociation);
        }

        $associatedProducts = $this->getAssociatedProducts(
            $associatedProductsIdentifiers,
            $limit,
            $from,
            $locale,
            $scope
        );

        $productModelLimit = $limit - $associatedProducts->count();
        $associatedProductModels = [];
        if ($productModelLimit > 0) {
            $productModelFrom = $from - count($associatedProductsIdentifiers) + $associatedProducts->count();
            $associatedProductModels = $this->getAssociatedProductModels(
                $associatedProductModelsIdentifiers,
                $productModelLimit,
                max($productModelFrom, 0),
                $locale,
                $scope
            );
        }

        $normalizedAssociatedProducts = $this->normalizeProductsAndProductModels(
            $associatedProducts,
            $associatedProductsIdentifiersFromParent,
            $locale,
            $scope
        );

        $normalizedAssociatedProductModels = $this->normalizeProductsAndProductModels(
            $associatedProductModels,
            $associatedProductModelsIdentifiersFromParent,
            $locale,
            $scope
        );

        $rows = ['totalRecords' => count($associatedProductsIdentifiers) + count($associatedProductModelsIdentifiers)];
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
    protected function getAssociatedProductIdentifiers(AssociationInterface $association): array
    {
        $identifiers = [];
        foreach ($association->getProducts() as $associatedProduct) {
            $identifiers[] = $associatedProduct->getIdentifier();
        }

        return $identifiers;
    }

    /**
     * @param AssociationInterface $association
     *
     * @return string[]
     */
    protected function getAssociatedProductModelIdentifiers(AssociationInterface $association): array
    {
        $identifiers = [];
        foreach ($association->getProductModels() as $associatedProduct) {
            $identifiers[] = $associatedProduct->getCode();
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
     * @return CursorInterface
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
        $pqb->addFilter('entity_type', Operators::EQUALS, ProductInterface::class);

        return $pqb->execute();
    }

    /**
     * @param array  $associatedProductModelsIdentifiers
     * @param int    $limit
     * @param int    $from
     * @param string $locale
     * @param string $scope
     *
     * @return CursorInterface
     */
    protected function getAssociatedProductModels(
        array $associatedProductModelsIdentifiers,
        $limit,
        $from,
        $locale,
        $scope
    ) {
        $pqb = $this->createQueryBuilder($limit, $from, $locale, $scope);
        $pqb->addFilter('identifier', Operators::IN_LIST, $associatedProductModelsIdentifiers);
        $pqb->addFilter('entity_type', Operators::EQUALS, ProductModelInterface::class);

        return $pqb->execute();
    }

    /**
     * @param CursorInterface $products
     * @param array           $identifiersFromInheritance
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
                $identifier = $product->getCode();
            } else {
                $identifier = $product->getIdentifier();
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
     * @param EntityWithAssociationsInterface $sourceProduct
     * @param mixed                           $associationTypeId
     * @return null|AssociationInterface
     */
    private function getAssociation(EntityWithAssociationsInterface $sourceProduct, $associationTypeId): ?AssociationInterface
    {
        foreach ($sourceProduct->getAllAssociations() as $association) {
            if ($association->getAssociationType()->getId() === (int)$associationTypeId) {
                return $association;
            }
        }

        return null;
    }
}
