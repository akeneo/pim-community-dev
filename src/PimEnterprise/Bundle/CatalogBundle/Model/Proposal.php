<?php

namespace PimEnterprise\Bundle\CatalogBundle\Model;

use Symfony\Component\Security\Core\User\UserInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Proposal of changes of a product
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Proposal
{
    /** @var int */
    protected $id;

    /** @var ProductInterface */
    protected $product;

    /** @var UserInterface */
    protected $createdBy;

    /** @var DateTime */
    protected $createdAt;

    /** @var array */
    protected $changes;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param ProductInterface $product
     */
    public function setProduct(ProductInterface $product)
    {
        $this->product = $product;
    }

    /**
     * @return ProductInterface
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param UserInterface $createdBy
     */
    public function setCreatedBy(UserInterface $createdBy)
    {
        $this->createdBy = $createdBy;
    }

    /**
     * @return UserInterface
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
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
     */
    public function setChanges(array $changes)
    {
        $this->changes = $changes;
    }

    /**
     * @return array
     */
    public function getChanges()
    {
        return $this->changes;
    }
}
