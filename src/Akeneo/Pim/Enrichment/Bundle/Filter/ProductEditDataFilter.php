<?php

namespace Akeneo\Pim\Enrichment\Bundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Product edit data filter
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductEditDataFilter implements CollectionFilterInterface
{
    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var CollectionFilterInterface */
    protected $productValuesFilter;

    /** @var array */
    protected $acls = [
        'family'       => 'pim_enrich_product_change_family',
        'categories'   => 'pim_enrich_product_categories_view',
        'enabled'      => 'pim_enrich_product_change_state',
        'associations' => 'pim_enrich_associations_view'
    ];

    /**
     * @param SecurityFacade            $securityFacade
     * @param CollectionFilterInterface $productValuesFilter
     */
    public function __construct(
        SecurityFacade $securityFacade,
        CollectionFilterInterface $productValuesFilter
    ) {
        $this->securityFacade = $securityFacade;
        $this->productValuesFilter = $productValuesFilter;
    }

    /**
     * Filter product data according to edit permissions
     *
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        $newProductData = [];
        $product = $options['product'];

        foreach ($collection as $type => $data) {
            if (!empty($type) && $this->isAllowed($product, $type)) {
                $newProductData[$type] = $this->filterData($type, $data);
            }
        }

        return $newProductData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return false;
    }

    private function filterData(string $type, $data)
    {
        if ('values' === $type) {
            $data = $this->productValuesFilter->filterCollection($data, 'pim.internal_api.product_values_data.edit');
        }

        return $data;
    }

    /**
     * Return whether the current user is allowed to update the given modification $type
     * on the given $product
     */
    protected function isAllowed(EntityWithValuesInterface $product, string $type): bool
    {
        $isAllowed = true;

        switch ($type) {
            case 'family':
                $isAllowed = $this->isAllowedToUpdateFamily($product);
                break;
            case 'groups':
                // We don't update groups from the PEF side
                $isAllowed = false;
                break;
            case 'categories':
                $isAllowed = $this->isAllowedToClassify($product);
                break;
            case 'enabled':
                $isAllowed = $this->isAllowedToUpdateStatus($product);
                break;
            case 'associations':
                $isAllowed = $this->isAllowedToUpdateAssociations($product);
                break;
            case 'values':
                $isAllowed = $this->isAllowedToUpdateValues($product);
                break;
        }

        return $isAllowed;
    }

    /**
     * Return whether the current user is allowed to update family of the product
     */
    protected function isAllowedToUpdateFamily(EntityWithValuesInterface $product): bool
    {
        return $this->checkAclForType('family');
    }

    /**
     * Return whether the current user is allowed to update categories of the product
     */
    protected function isAllowedToClassify(EntityWithValuesInterface $product): bool
    {
        return $this->checkAclForType('categories');
    }

    /**
     * Return whether the current user is allowed to update status of the product
     */
    protected function isAllowedToUpdateStatus(EntityWithValuesInterface $product): bool
    {
        return $this->checkAclForType('enabled');
    }

    /**
     * Return whether the current user is allowed to update associations of the product
     */
    protected function isAllowedToUpdateAssociations(EntityWithValuesInterface $product): bool
    {
        return $this->checkAclForType('associations');
    }

    /**
     * Return whether the current user is allowed to update product values of the product
     */
    protected function isAllowedToUpdateValues(EntityWithValuesInterface $product): bool
    {
        return $this->checkAclForType('values');
    }

    /**
     * Return whether the current user has ACL to do the given modification $type on the product
     */
    protected function checkAclForType(string $type): bool
    {
        $acl = $this->getAclForType($type);

        return null === $acl || $this->securityFacade->isGranted($acl);
    }

    /**
     * Return which ACL should be used to filter data of specified type.
     */
    protected function getAclForType(string $type): ?string
    {
        return isset($this->acls[$type]) ? $this->acls[$type] : null;
    }
}
