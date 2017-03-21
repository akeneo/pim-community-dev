<?php

namespace Pim\Bundle\EnrichBundle\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;

/**
 * Variant group edit data filter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupEditDataFilter implements CollectionFilterInterface
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
     * Filter variabnt group data according to edit permissions
     *
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        $filteredData = [];

        foreach ($collection as $type => $data) {
            if ($this->isAllowed($type)) {
                $filteredData[$type] = $this->filterData($type, $data);
            }
        }

        return $filteredData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return false;
    }

    /**
     * Filter & return the given $data for the given $type
     *
     * @param string $type
     * @param mixed  $data
     *
     * @return mixed
     */
    protected function filterData($type, $data)
    {
        if ('values' === $type) {
            $data = $this->productValuesFilter->filterCollection($data, 'pim.internal_api.product_values_data.edit');
        }

        return $data;
    }

    /**
     * Return whether the current user is allowed to update the given modification $type
     *
     * @param string $type
     *
     * @return bool
     */
    protected function isAllowed($type)
    {
        $isAllowed = true;

        switch ($type) {
            case 'axis':
            case 'code':
            case 'type':
                $isAllowed = false;
                break;
            case 'translations':
                $isAllowed = true;
                break;
            case 'values':
                $isAllowed = $this->securityFacade->isGranted('pim_enrich_variant_group_edit_attributes');
                break;
        }

        return $isAllowed;
    }
}
