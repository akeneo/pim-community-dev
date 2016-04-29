<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Model;

use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Product draft interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ProductDraftInterface
{
    const IN_PROGRESS = 0;
    const READY       = 1;

    const CHANGE_DRAFT     = 'draft';
    const CHANGE_TO_REVIEW = 'to_review';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param ProductInterface $product
     *
     * @return ProductDraftInterface
     */
    public function setProduct(ProductInterface $product);

    /**
     * @return ProductInterface
     */
    public function getProduct();

    /**
     * @param string $author
     *
     * @return ProductDraftInterface
     */
    public function setAuthor($author);

    /**
     * @return string
     */
    public function getAuthor();

    /**
     * @param \DateTime $createdAt
     *
     * @return ProductDraftInterface
     */
    public function setCreatedAt(\DateTime $createdAt);

    /**
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * @param array $changes
     *
     * @return ProductDraftInterface
     */
    public function setChanges(array $changes);

    /**
     * @return array
     */
    public function getChanges();

    /**
     * @return bool
     */
    public function hasChanges();

    /**
     * Return only changes with the given $status
     *
     * @param string $status
     *
     * @return array
     */
    public function getChangesByStatus($status);

    /**
     * Return only changes to review
     *
     * @return array
     */
    public function getChangesToReview();

    /**
     * Get the change associated to the the given attribute code if it exists.
     *
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     *
     * @return array|null
     */
    public function getChange($fieldCode, $localeCode, $channelCode);

    /**
     * Remove the change associated to the attribute code if it exists
     *
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     */
    public function removeChange($fieldCode, $localeCode, $channelCode);

    /**
     * Get the review status associated to the the given attribute code if it exists.
     *
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     *
     * @return array|null
     */
    public function getReviewStatusForChange($fieldCode, $localeCode, $channelCode);

    /**
     * Set the review status associated to the the given attribute code if it exists.
     *
     * @param string $status
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     *
     * @return ProductDraftInterface
     */
    public function setReviewStatusForChange($status, $fieldCode, $localeCode, $channelCode);

    /**
     * Set all review statuses to the specified one.
     *
     * @param string $status
     *
     * @return ProductDraftInterface
     */
    public function setAllReviewStatuses($status);

    /**
     * Remove the review status associated to the attribute code if it exists
     *
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     */
    public function removeReviewStatusForChange($fieldCode, $localeCode, $channelCode);

    /**
     * Check if all review statuses matches the specified one
     *
     * @param string $status
     *
     * @return bool
     */
    public function areAllReviewStatusesTo($status);

    /**
     * Mark the draft as in progress
     */
    public function markAsInProgress();

    /**
     * Mark the draft as ready
     */
    public function markAsReady();

    /**
     * Get status of the draft. Either IN_PROGRESS or READY for review.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Whether or not product draft is in progress
     *
     * @return bool
     */
    public function isInProgress();

    /**
     * Set the category ids
     * NB: Only used with MongoDB
     *
     * @param array $categoryIds
     */
    public function setCategoryIds(array $categoryIds);

    /**
     * Get the product category ids
     * NB: Only used with MongoDB
     *
     * @return array
     */
    public function getCategoryIds();

    /**
     * Removes a category id
     *
     * @param int $categoryId
     */
    public function removeCategoryId($categoryId);

    /**
     * @param string $dataLocale
     *
     * @return ProductDraftInterface
     */
    public function setDataLocale($dataLocale);

    /**
     * @return string
     */
    public function getDataLocale();
}
