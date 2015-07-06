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

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Product draft interface
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
interface ProductDraftInterface
{
    /** @staticvar integer */
    const IN_PROGRESS = 0;

    /** @staticvar integer */
    const READY = 1;

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
     * Set status
     *
     * @param int $status
     */
    public function setStatus($status);

    /**
     * Get status
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
