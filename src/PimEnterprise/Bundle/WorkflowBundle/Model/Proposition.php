<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Proposition of changes of a product
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Proposition
{
    /** @staticvar integer */
    const IN_PROGRESS = 0;

    /** @staticvar integer */
    const READY = 1;

    /** @var integer */
    protected $id;

    /** @var ProductInterface */
    protected $product;

    /** @var string */
    protected $author;

    /** @var \DateTime */
    protected $createdAt;

    /** @var array */
    protected $changes = [];

    /** @var integer */
    protected $status;

    /** @var array */
    protected $categoryIds = [];

    /** @var string not persisted, used to contextualize the proposition */
    protected $dataLocale = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->status = self::IN_PROGRESS;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ProductInterface $product
     *
     * @return Proposition
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $author
     *
     * @return Proposition
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return Proposition
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param array $changes
     *
     * @return Proposition
     */
    public function setChanges(array $changes)
    {
        $this->changes = $changes;

        return $this;
    }

    /**
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * Set status
     *
     * @param integer $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * Get status
     *
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Wether or not proposition is in progress
     *
     * @return boolean
     */
    public function isInProgress()
    {
        return self::IN_PROGRESS === $this->status;
    }

    /**
     * Set the category ids
     * NB: Only used with MongoDB
     *
     * @param array $categoryIds
     */
    public function setCategoryIds(array $categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * Get the product category ids
     * NB: Only used with MongoDB
     *
     * @return array
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * Removes a category id
     *
     * @param integer $categoryId
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
     * @param string $dataLocale
     *
     * @return Proposition
     */
    public function setDataLocale($dataLocale)
    {
        $this->dataLocale = $dataLocale;

        return $this;
    }

    /**
     * @return string
     */
    public function getDataLocale()
    {
        return $this->dataLocale;
    }
}
