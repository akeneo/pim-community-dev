<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

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
    public function getChange($changeCode, $localeCode, $channelCode)
    {
        if (!isset($this->changes['values'])) {
            return null;
        }

        if (!isset($this->changes['values'][$changeCode])) {
            return null;
        }

        foreach ($this->changes['values'][$changeCode] as $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                return $change['data'];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function removeChange($changeCode, $localeCode, $channelCode)
    {
        if (!isset($this->changes['values'])) {
            return;
        }

        if (!isset($this->changes['values'][$changeCode])) {
            return;
        }

        foreach ($this->changes['values'][$changeCode] as $index => $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                unset($this->changes['values'][$changeCode][$index]);
                $this->removeReviewStatusForChange($channelCode, $localeCode, $channelCode);
            }
        }

        $this->changes['values'][$changeCode] = array_values($this->changes['values'][$changeCode]);

        if (empty($this->changes['values'][$changeCode])) {
            unset($this->changes['values'][$changeCode]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getReviewStatusForChange($changeCode, $localeCode, $channelCode)
    {
        if (!isset($this->changes['review_statuses'][$changeCode])) {
            return null;
        }

        foreach ($this->changes['review_statuses'][$changeCode] as $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                return $change['status'];
            }
        }

        return null;
    }


    /**
     * {@inheritdoc}
     */
    public function setReviewStatusForChange($status, $changeCode, $localeCode, $channelCode)
    {
        if (!isset($this->changes['review_statuses'][$changeCode])) {
            //TODO: throw exception
            return;
        }

        foreach ($this->changes['review_statuses'][$changeCode] as $index => $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                $this->changes['review_statuses'][$changeCode][$index]['status'] = $status;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeReviewStatusForChange($changeCode, $localeCode, $channelCode)
    {
        if (!isset($this->changes['review_statuses'][$changeCode])) {
            return;
        }

        foreach ($this->changes['review_statuses'][$changeCode] as $index => $change) {
            if ($localeCode === $change['locale'] && $channelCode === $change['scope']) {
                unset($this->changes['review_statuses'][$changeCode][$index]);
            }
        }

        $this->changes['review_statuses'][$changeCode] = array_values($this->changes['review_statuses'][$changeCode]);

        if (empty($this->changes['review_statuses'][$changeCode])) {
            unset($this->changes['review_statuses'][$changeCode]);
        }
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
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
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
