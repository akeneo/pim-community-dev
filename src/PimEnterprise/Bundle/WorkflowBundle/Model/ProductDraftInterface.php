<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
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
 * Product draft interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ProductDraftInterface
{
    const IN_PROGRESS = 0;
    const READY = 1;

    const CHANGE_TO_REVIEW = 'to_review';
    const CHANGE_REJECTED = 'rejected';
    const CHANGE_APPROVED = 'approved';
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
     * @param AttributeInterface    $attribute
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null  $locale
     *
     * @return array|null
     */
    public function getChangeForAttribute(
        AttributeInterface $attribute,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    );

    /**
     * Remove the change associated to the attribute if it exists
     *
     * @param AttributeInterface    $attribute
     * @param ChannelInterface|null $channel
     * @param LocaleInterface|null  $locale
     */
    public function removeChangeForAttribute(
        AttributeInterface $attribute,
        ChannelInterface $channel = null,
        LocaleInterface $locale = null
    );

    /**
     * Set status of the draft. Either IN_PROGRESS or READY for review.
     *
     * @param int $status
     */
    public function setStatus($status);

    /**
     * Get status of the draft. Either IN_PROGRESS or READY for review.
     *
     * @return int
     */
    public function getStatus();

    /**
     * TODO: could be removed if merged with setChanges
     *
     * Set statuses of the changes
     *
     * @param array $statuses
     */
    public function setReviewStatuses(array $statuses);

    /**
     * TODO: could be removed if merged with getChanges
     *
     * Get statuses of the changes
     *
     * @return array
     */
    public function getReviewStatuses();

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
