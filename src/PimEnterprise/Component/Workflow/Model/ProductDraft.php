<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Workflow\Model;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * Product draft
 *
 * @author Gildas Quemener <gildas@akeneo.com>
 */
class ProductDraft implements ProductDraftInterface
{
    /** @var int */
    protected $id;

    /** @var ProductInterface */
    protected $product;

    /** @var string */
    protected $author;

    /** @var \DateTime */
    protected $createdAt;

    /** @var array */
    protected $changes = [];

    /** @var int */
    protected $status;

    /** @var array */
    protected $categoryIds = [];

    /** @var string not persisted, used to contextualize the product draft */
    protected $dataLocale = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::IN_PROGRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setChanges(array $changes)
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * {@inheritdoc}
     */
    public function getChangesByStatus($status)
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
    public function getChangesToReview()
    {
        return $this->getChangesByStatus(self::CHANGE_TO_REVIEW);
    }

    /**
     * {@inheritdoc}
     */
    public function getChange($fieldCode, $localeCode, $channelCode)
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
    public function removeChange($fieldCode, $localeCode, $channelCode)
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
    public function getReviewStatusForChange($fieldCode, $localeCode, $channelCode)
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
    public function setReviewStatusForChange($status, $fieldCode, $localeCode, $channelCode)
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
    public function setAllReviewStatuses($status)
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
    public function removeReviewStatusForChange($fieldCode, $localeCode, $channelCode)
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
    public function areAllReviewStatusesTo($status)
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
    public function hasChanges()
    {
        return !empty($this->changes) && !empty($this->changes['values']);
    }

    /**
     * {@inheritdoc}
     */
    public function markAsInProgress()
    {
        $this->status = self::IN_PROGRESS;
    }

    /**
     * {@inheritdoc}
     */
    public function markAsReady()
    {
        $this->status = self::READY;
    }

    /**
     * {@inheritdoc}
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function isInProgress()
    {
        return self::IN_PROGRESS === $this->status;
    }

    /**
     * {@inheritdoc}
     */
    public function setCategoryIds(array $categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * {@inheritdoc}
     */
    public function removeCategoryId($categoryId)
    {
        if (false === $key = array_search($categoryId, $this->categoryIds)) {
            return;
        }

        unset($this->categoryIds[$key]);
        $this->categoryIds = array_values($this->categoryIds);
    }

    /**
     * {@inheritdoc}
     */
    public function setDataLocale($dataLocale)
    {
        $this->dataLocale = $dataLocale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDataLocale()
    {
        return $this->dataLocale;
    }
}
