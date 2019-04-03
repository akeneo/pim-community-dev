<?php

namespace Akeneo\Pim\Enrichment\Bundle\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
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
            if ($this->isAllowed($product, $type)) {
                $newProductData[$type] = $data;
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

    /**
     * Return whether the current user is allowed to update the given modification $type
     * on the given $product
     *
     * @param ProductInterface $product
     * @param string           $type
     *
     * @return bool
     */
    protected function isAllowed(ProductInterface $product, $type)
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
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToUpdateFamily(ProductInterface $product)
    {
        return $this->checkAclForType('family');
    }

    /**
     * Return whether the current user is allowed to update categories of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToClassify(ProductInterface $product)
    {
        return $this->checkAclForType('categories');
    }

    /**
     * Return whether the current user is allowed to update status of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToUpdateStatus(ProductInterface $product)
    {
        return $this->checkAclForType('enabled');
    }

    /**
     * Return whether the current user is allowed to update associations of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToUpdateAssociations(ProductInterface $product)
    {
        return $this->checkAclForType('associations');
    }

    /**
     * Return whether the current user is allowed to update product values of the product
     *
     * @param ProductInterface $product
     *
     * @return bool
     */
    protected function isAllowedToUpdateValues(ProductInterface $product)
    {
        return $this->checkAclForType('values');
    }

    /**
     * Return whether the current user has ACL to do the given modification $type on the product
     *
     * @param string $type
     *
     * @return bool
     */
    protected function checkAclForType(string $type): bool
    {
        $acl = $this->getAclForType($type);

        return null === $acl || $this->securityFacade->isGranted($acl);
    }

    /**
     * Return which ACL should be used to filter data of specified type.
     *
     * @param string $type
     *
     * @return string|null
     */
    protected function getAclForType(string $type): ?string
    {
        return isset($this->acls[$type]) ? $this->acls[$type] : null;
    }
}
