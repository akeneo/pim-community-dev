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
     *
     * @throws \LogicException
     */
    public function getChangeForAttribute(
        AttributeInterface $attribute,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        $code = $attribute->getCode();

        if ($attribute->isScopable() && null === $channel) {
            throw new \LogicException(sprintf(
                'Trying to get changes for the scopable attribute "%s" without scope.',
                $code
            ));
        }

        if ($attribute->isLocalizable() && null === $locale) {
            throw new \LogicException(sprintf(
                'Trying to get changes for the localizable attribute "%s" without locale.',
                $code
            ));
        }

        if (!isset($this->changes['values'])) {
            return null;
        }

        if (!isset($this->changes['values'][$code])) {
            return null;
        }

        foreach ($this->changes['values'][$code] as $change) {
            if (
                (!$attribute->isLocalizable() || $change['locale'] === $locale->getCode())
                && (!$attribute->isScopable() || $change['scope'] === $channel->getCode())
            ) {
                return $change['data'];
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \LogicException
     */
    public function removeChangeForAttribute(
        AttributeInterface $attribute,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    ) {
        $code = $attribute->getCode();

        if ($attribute->isScopable() && null === $channel) {
            throw new \LogicException(sprintf(
                'Trying to get changes for the scopable attribute "%s" without scope.',
                $code
            ));
        }

        if ($attribute->isLocalizable() && null === $locale) {
            throw new \LogicException(sprintf(
                'Trying to get changes for the localizable attribute "%s" without locale.',
                $code
            ));
        }

        if (!isset($this->changes['values'])) {
            return;
        }

        if (!isset($this->changes['values'][$code])) {
            return;
        }

        foreach ($this->changes['values'][$code] as $index => $change) {
            if (
                (!$attribute->isLocalizable() || $change['locale'] === $locale->getCode())
                && (!$attribute->isScopable() || $change['scope'] === $channel->getCode())
            ) {
                unset($this->changes['values'][$code][$index]);
            }
        }

        $this->changes['values'][$code] = array_values($this->changes['values'][$code]);

        if (empty($this->changes['values'][$code])) {
            unset($this->changes['values'][$code]);
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
