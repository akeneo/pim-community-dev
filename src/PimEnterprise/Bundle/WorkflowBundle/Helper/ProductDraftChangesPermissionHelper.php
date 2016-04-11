<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Helper;

use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use PimEnterprise\Component\Workflow\Model\ProductDraftInterface;

/**
 * @author Adrien Pétremann <adrien.petremann@akeneo.com>
 */
class ProductDraftChangesPermissionHelper
{
    /** @var CollectionFilterInterface */
    protected $valuesFilter;

    /**
     * @param CollectionFilterInterface $valuesFilter
     */
    public function __construct(CollectionFilterInterface $valuesFilter)
    {
        $this->valuesFilter = $valuesFilter;
    }

    /**
     * Return whether the user can edit ALL changes in review of the given $productDraft
     *
     * @param ProductDraftInterface $productDraft
     *
     * @return bool
     */
    public function canEditAllChangesToReview(ProductDraftInterface $productDraft)
    {
        $changes = $productDraft->getChangesByStatus(ProductDraftInterface::CHANGE_TO_REVIEW);
        $filteredValues = $this->valuesFilter->filterCollection($changes['values'], 'pim.internal_api.attribute.edit');

        return $filteredValues == $changes['values'];
    }

    /**
     * Return whether the user can edit at least one change in review of the given $productDraft
     *
     * @param ProductDraftInterface $productDraft
     *
     * @return bool
     */
    public function canEditOneChangeToReview(ProductDraftInterface $productDraft)
    {
        $changes = $productDraft->getChangesByStatus(ProductDraftInterface::CHANGE_TO_REVIEW);
        $filteredValues = $this->valuesFilter->filterCollection($changes['values'], 'pim.internal_api.attribute.edit');

        return !empty($filteredValues);
    }

    /**
     * Return whether the user can edit all changes in progress of the given $productDraft
     *
     * @param ProductDraftInterface $productDraft
     *
     * @return bool
     */
    public function canEditAllChangesDraft(ProductDraftInterface $productDraft)
    {
        $changes = $productDraft->getChangesByStatus(ProductDraftInterface::CHANGE_DRAFT);
        $filteredValues = $this->valuesFilter->filterCollection($changes['values'], 'pim.internal_api.attribute.edit');

        return $filteredValues == $changes['values'];
    }

    /**
     * Return whether the user can edit at least one change in progress of the given $productDraft
     *
     * @param ProductDraftInterface $productDraft
     *
     * @return bool
     */
    public function canEditOneChangeDraft(ProductDraftInterface $productDraft)
    {
        $changes = $productDraft->getChangesByStatus(ProductDraftInterface::CHANGE_DRAFT);
        $filteredValues = $this->valuesFilter->filterCollection($changes['values'], 'pim.internal_api.attribute.edit');

        return !empty($filteredValues);
    }
}
