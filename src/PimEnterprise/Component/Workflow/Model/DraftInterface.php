<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Model;

/**
 * Draft interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface DraftInterface
{
    const IN_PROGRESS = 0;
    const READY = 1;

    const CHANGE_DRAFT = 'draft';
    const CHANGE_TO_REVIEW = 'to_review';

    /**
     * @param string $author
     *
     * @return DraftInterface
     */
    public function setAuthor(string $author): DraftInterface;

    /**
     * @return string
     */
    public function getAuthor(): string;

    /**
     * @param \DateTime $createdAt
     *
     * @return DraftInterface
     */
    public function setCreatedAt(\DateTime $createdAt): DraftInterface;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;

    /**
     * @param array $changes
     *
     * @return DraftInterface
     */
    public function setChanges(array $changes): DraftInterface;

    /**
     * @return array
     */
    public function getChanges(): array;

    /**
     * @return bool
     */
    public function hasChanges(): bool;

    /**
     * Return only changes with the given $status
     *
     * @param string $status
     *
     * @return array
     */
    public function getChangesByStatus(string $status): array;

    /**
     * Return only changes to review
     *
     * @return array
     */
    public function getChangesToReview(): array;

    /**
     * Get the change associated to the the given attribute code if it exists.
     *
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     *
     * @return array|null
     */
    public function getChange(string $fieldCode, string $localeCode, string $channelCode): ?array;

    /**
     * Remove the change associated to the attribute code if it exists
     *
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     */
    public function removeChange(string $fieldCode, string $localeCode, string $channelCode);

    /**
     * Get the review status associated to the the given attribute code if it exists.
     *
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     *
     * @return array|null
     */
    public function getReviewStatusForChange(string $fieldCode, string $localeCode, string $channelCode): ?array;

    /**
     * Set the review status associated to the the given attribute code if it exists.
     *
     * @param string $status
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     *
     * @return DraftInterface
     */
    public function setReviewStatusForChange(string $status, string $fieldCode, string $localeCode, string $channelCode): DraftInterface;

    /**
     * Set all review statuses to the specified one.
     *
     * @param string $status
     *
     * @return DraftInterface
     */
    public function setAllReviewStatuses(string $status): DraftInterface;

    /**
     * Remove the review status associated to the attribute code if it exists
     *
     * @param string $fieldCode
     * @param string $localeCode
     * @param string $channelCode
     */
    public function removeReviewStatusForChange(string $fieldCode, string $localeCode, string $channelCode);

    /**
     * Check if all review statuses matches the specified one
     *
     * @param string $status
     *
     * @return bool
     */
    public function areAllReviewStatusesTo(string $status): bool;

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
     * @return string
     */
    public function getStatus(): string;

    /**
     * Whether or not product draft is in progress
     *
     * @return bool
     */
    public function isInProgress(): bool;
}
