<?php

declare(strict_types=1);

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
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueCollectionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * Product model model draft
 */
class ProductModelDraft implements EntityWithValuesDraftInterface
{
    /** @var int */
    protected $id;

    /** @var ProductModelInterface */
    protected $entityWithValues;

    /** @var string */
    protected $author;

    /** @var \DateTime */
    protected $createdAt;

    /** @var ValueCollectionInterface */
    protected $values;

    /** @var array */
    protected $rawValues;

    /** @var array */
    protected $changes = [];

    /** @var int */
    protected $status;

    /** @var array */
    protected $categoryIds = [];

    /** @var string not persisted, used to contextualize the productModel draft */
    protected $dataLocale = null;

    public function __construct()
    {
        $this->status = EntityWithValuesDraftInterface::IN_PROGRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier(): string
    {
        return (string) $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function setEntityWithValue(EntityWithValuesInterface $entityWithValues): EntityWithValuesDraftInterface
    {
        $this->entityWithValues = $entityWithValues;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEntityWithValue(): EntityWithValuesInterface
    {
        return $this->entityWithValues;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthor(string $author): EntityWithValuesDraftInterface
    {
        $this->author = $author;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor(): string
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt): EntityWithValuesDraftInterface
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setRawValues(array $rawValues): EntityWithValuesDraftInterface
    {
        $this->rawValues = $rawValues;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawValues(): array
    {
        return $this->rawValues;
    }

    /**
     * {@inheritdoc}
     */
    public function setChanges(array $changes): EntityWithValuesDraftInterface
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges(): array
    {
        return $this->changes;
    }

    /**
     * {@inheritdoc}
     */
    public function getChangesByStatus(string $status): array
    {
        $changes = $this->changes;

        if (!isset($changes['values'])) {
            return [];
        }

        foreach ($changes['values'] as $code => $changeset) {
            foreach ($changeset as $index => $change) {
                $changeStatus = $this->getReviewStatusForChange($code, $change['locale'], $change['scope']);
                if ($status !== $changeStatus) {
                    unset($changes['values'][$code][$index]);
                }
            }
        }

        $changes['values'] = array_filter($changes['values']);

        return $changes;
    }

    /**
     * {@inheritdoc}
     */
    public function getChangesToReview(): array
    {
        return $this->getChangesByStatus(self::CHANGE_TO_REVIEW);
    }

    /**
     * {@inheritdoc}
     */
    public function getChange(string $fieldCode, ?string $localeCode, ?string $channelCode)
    {
        if (!isset($this->changes['values'])) {
            return null;
        }

        if (!isset($this->changes['values'][$fieldCode])) {
            return null;
        }

        foreach ($this->changes['values'][$fieldCode] as $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                return $change['data'];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChange(string $fieldCode, ?string $localeCode, ?string $channelCode)
    {
        if (!isset($this->changes['values'])) {
            return;
        }

        if (!isset($this->changes['values'][$fieldCode])) {
            return;
        }

        foreach ($this->changes['values'][$fieldCode] as $index => $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                unset($this->changes['values'][$fieldCode][$index]);
                $this->removeReviewStatusForChange($fieldCode, $localeCode, $channelCode);
            }
        }

        $this->changes['values'][$fieldCode] = array_values($this->changes['values'][$fieldCode]);

        if (empty($this->changes['values'][$fieldCode])) {
            unset($this->changes['values'][$fieldCode]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getReviewStatusForChange(?string $fieldCode, ?string $localeCode, ?string $channelCode): ?string
    {
        if (!isset($this->changes['review_statuses'][$fieldCode])) {
            return null;
        }

        foreach ($this->changes['review_statuses'][$fieldCode] as $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                return $change['status'];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function setReviewStatusForChange(string $status, string $fieldCode, ?string $localeCode, ?string $channelCode): EntityWithValuesDraftInterface
    {
        if (self::CHANGE_DRAFT !== $status && self::CHANGE_TO_REVIEW !== $status) {
            throw new \LogicException(sprintf('"%s" is not a valid review status', $status));
        }

        if (!isset($this->changes['review_statuses'][$fieldCode])) {
            throw new \LogicException(sprintf('There is no review status for code "%s"', $fieldCode));
        }

        foreach ($this->changes['review_statuses'][$fieldCode] as $index => $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                $this->changes['review_statuses'][$fieldCode][$index]['status'] = $status;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function setAllReviewStatuses(string $status): EntityWithValuesDraftInterface
    {
        if (self::CHANGE_DRAFT !== $status && self::CHANGE_TO_REVIEW !== $status) {
            throw new \LogicException(sprintf('"%s" is not a valid review status', $status));
        }

        $statuses = $this->changes['values'];
        foreach ($statuses as &$items) {
            foreach ($items as &$item) {
                $item['status'] = $status;
                unset($item['data']);
            }
        }

        $this->changes['review_statuses'] = $statuses;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeReviewStatusForChange(string $fieldCode, ?string $localeCode, ?string $channelCode): void
    {
        if (!isset($this->changes['review_statuses'][$fieldCode])) {
            return;
        }

        foreach ($this->changes['review_statuses'][$fieldCode] as $index => $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                unset($this->changes['review_statuses'][$fieldCode][$index]);
            }
        }

        $this->changes['review_statuses'][$fieldCode] = array_values($this->changes['review_statuses'][$fieldCode]);

        if (empty($this->changes['review_statuses'][$fieldCode])) {
            unset($this->changes['review_statuses'][$fieldCode]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function areAllReviewStatusesTo(string $status): bool
    {
        foreach ($this->changes['review_statuses'] as $items) {
            foreach ($items as $item) {
                if ($status !== $item['status']) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function hasChanges(): bool
    {
        return !empty($this->changes) && !empty($this->changes['values']);
    }

    /**
     * {@inheritdoc}
     */
    public function markAsInProgress(): void
    {
        $this->status = self::IN_PROGRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsReady(): void
    {
        $this->status = self::READY;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function isInProgress(): bool
    {
        return self::IN_PROGRESS === $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes(): array
    {
        return $this->getValues()->getAttributes();
    }

    /**
     * {@inheritdoc}
     */
    public function getValues(): ValueCollectionInterface
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function setValues(ValueCollectionInterface $values): EntityWithValuesDraftInterface
    {
        $this->values = $values;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addValue(ValueInterface $value)
    {
        $this->values->add($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeValue(ValueInterface $value)
    {
        $this->values->remove($value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsedAttributeCodes(): array
    {
        return $this->values->getAttributeCodes();
    }

    /**
     * {@inheritdoc}
     */
    public function getValue($attributeCode, $localeCode = null, $scopeCode = null)
    {
        return $this->getValues()->getByCodes($attributeCode, $scopeCode, $localeCode);
    }

    /**
     * {@inheritdoc}
     */
    public function hasAttribute(string $attributeCode): bool
    {
        return in_array($attributeCode, $this->getValues()->getAttributeCodes(), true);
    }
}
