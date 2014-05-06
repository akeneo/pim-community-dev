<?php

namespace PimEnterprise\Bundle\CatalogBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * Revision of a product
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class Revision
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
    protected $newValues;

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
     * @param array $newValues
     */
    public function setNewValues(array $newValues)
    {
        $this->newValues = $newValues;
    }

    /**
     * @return array
     */
    public function getNewValues()
    {
        return $this->newValues;
    }
}
