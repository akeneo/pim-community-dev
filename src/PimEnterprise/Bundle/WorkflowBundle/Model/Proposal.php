<?php

namespace PimEnterprise\Bundle\WorkflowBundle\Model;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Proposal of changes of a product
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Proposal
{
    /** @staticvar integer */
    const WAITING = 0;

    /** @staticvar integer */
    const APPROVED = 1;

    /** @staticvar integer */
    const REFUSED = 2;

    /** @var integer */
    protected $id;

    /** @var ProductInterface */
    protected $product;

    /** @var string */
    protected $author;

    /** @var DateTime */
    protected $createdAt;

    /** @var array */
    protected $changes = [];

    /** @var integer */
    protected $status = self::WAITING;

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
     * @return Proposal
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
     * @return Proposal
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
     * @param DateTime $createdAt
     *
     * @return Proposal
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param array $changes
     *
     * @return Proposal
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
     * @param integer $status
     *
     * @return Proposal
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return integer
     */
    public function getStatus()
    {
        return $this->status;
    }
}
