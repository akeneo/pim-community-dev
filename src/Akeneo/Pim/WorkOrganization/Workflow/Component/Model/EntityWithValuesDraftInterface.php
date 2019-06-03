<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;

/**
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface EntityWithValuesDraftInterface extends EntityWithValuesInterface
{
    const IN_PROGRESS = 0;
    const READY = 1;

    const CHANGE_DRAFT = 'draft';
    const CHANGE_TO_REVIEW = 'to_review';

    /**
     * @return int
     */
    public function getId(): ?int;

    /**
     * @param string $author
     *
     * @return EntityWithValuesDraftInterface
     */
    public function setAuthor(string $author): EntityWithValuesDraftInterface;

    /**
     * @return string
     */
    public function getAuthor(): string;

    /**
     * @param \DateTime $createdAt
     *
     * @return EntityWithValuesDraftInterface
     */
    public function setCreatedAt(\DateTime $createdAt): EntityWithValuesDraftInterface;

    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime;

    /**
     * @param array $changes
     *
     * @return EntityWithValuesDraftInterface
     */
    public function setChanges(array $changes): EntityWithValuesDraftInterface;

    /**
     * @return array
     */
    public function getChanges(): ?array;

    /**
     * @return bool
     */
    public function hasChanges(): bool;

    /**
     * Return only changes with the given $status
     */
    public function getChangesByStatus(string $status): array;

    /**
     * Return only changes to review
     */
    public function getChangesToReview(): array;

    /**
     * Get the change associated to the the given attribute code if it exists.
     */
    public function getChange(string $fieldCode, ?string $localeCode, ?string $channelCode);

    /**
     * Remove the change associated to the attribute code if it exists
     */
    public function removeChange(string $fieldCode, ?string $localeCode, ?string $channelCode);

    /**
     * Get the review status associated to the the given attribute code if it exists.
     */
    public function getReviewStatusForChange(?string $fieldCode, ?string $localeCode, ?string $channelCode): ?string;

    /**
     * Set the review status associated to the the given attribute code if it exists.
     */
    public function setReviewStatusForChange(string $status, string $fieldCode, ?string $localeCode, ?string $channelCode): EntityWithValuesDraftInterface;

    /**
     * Set all review statuses to the specified one.
     */
    public function setAllReviewStatuses(string $status): EntityWithValuesDraftInterface;

    /**
     * Remove the review status associated to the attribute code if it exists
     */
    public function removeReviewStatusForChange(string $fieldCode, ?string $localeCode, ?string $channelCode): void;

    /**
     * Check if all review statuses matches the specified one
     */
    public function areAllReviewStatusesTo(string $status): bool;

    /**
     * Mark the draft as in progress
     */
    public function markAsInProgress(): void;

    /**
     * Mark the draft as ready
     */
    public function markAsReady(): void;

    /**
     * Get status of the draft. Either IN_PROGRESS or READY for review.
     */
    public function getStatus(): int;

    /**
     * Whether or not product draft is in progress
     */
    public function isInProgress(): bool;

    /**
     * @param EntityWithValuesInterface $entityWithValues
     *
     * @return EntityWithValuesDraftInterface
     */
    public function setEntityWithValue(EntityWithValuesInterface $entityWithValues): EntityWithValuesDraftInterface;

    /**
     * @return EntityWithValuesInterface
     */
    public function getEntityWithValue(): EntityWithValuesInterface;
}
