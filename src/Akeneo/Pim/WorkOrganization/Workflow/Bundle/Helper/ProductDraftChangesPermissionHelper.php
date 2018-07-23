<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Helper;

use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;

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
     * @param EntityWithValuesDraftInterface $productDraft
     *
     * @return bool
     */
    public function canEditAllChangesToReview(EntityWithValuesDraftInterface $productDraft)
    {
        $changes = $productDraft->getChangesByStatus(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $filteredValues = $this->valuesFilter->filterCollection($changes['values'], 'pim.internal_api.attribute.edit');

        return $filteredValues == $changes['values'];
    }

    /**
     * Return whether the user can edit at least one change in review of the given $productDraft
     *
     * @param EntityWithValuesDraftInterface $productDraft
     *
     * @return bool
     */
    public function canEditOneChangeToReview(EntityWithValuesDraftInterface $productDraft)
    {
        $changes = $productDraft->getChangesByStatus(EntityWithValuesDraftInterface::CHANGE_TO_REVIEW);
        $filteredValues = $this->valuesFilter->filterCollection($changes['values'], 'pim.internal_api.attribute.edit');

        return !empty($filteredValues);
    }

    /**
     * Return whether the user can edit all changes in progress of the given $productDraft
     *
     * @param EntityWithValuesDraftInterface $productDraft
     *
     * @return bool
     */
    public function canEditAllChangesDraft(EntityWithValuesDraftInterface $productDraft)
    {
        $changes = $productDraft->getChangesByStatus(EntityWithValuesDraftInterface::CHANGE_DRAFT);
        $filteredValues = $this->valuesFilter->filterCollection($changes['values'], 'pim.internal_api.attribute.edit');

        return $filteredValues == $changes['values'];
    }

    /**
     * Return whether the user can edit at least one change in progress of the given $productDraft
     *
     * @param EntityWithValuesDraftInterface $productDraft
     *
     * @return bool
     */
    public function canEditOneChangeDraft(EntityWithValuesDraftInterface $productDraft)
    {
        $changes = $productDraft->getChangesByStatus(EntityWithValuesDraftInterface::CHANGE_DRAFT);
        $filteredValues = $this->valuesFilter->filterCollection($changes['values'], 'pim.internal_api.attribute.edit');

        return !empty($filteredValues);
    }
}
