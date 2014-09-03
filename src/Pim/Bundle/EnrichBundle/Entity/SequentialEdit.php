<?php

namespace Pim\Bundle\EnrichBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * SequentialEdit entity
 *
 * @author    Rémy Bétus <remy.betus@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEdit
{
    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var integer[] $productSet
     */
    protected $productSet = [];

    /**
     * @var UserInterface $user
     */
    protected $user;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return SequentialEdit
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Set user
     *
     * @param UserInterface $user
     *
     * @return SequentialEdit
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return UserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get product set
     *
     * @return integer[]
     */
    public function getProductSet()
    {
        return $this->productSet;
    }

    /**
     * Set product set
     *
     * @param integer[] $productSet
     *
     * @return SequentialEdit
     */
    public function setProductSet(array $productSet)
    {
        $this->productSet = $productSet;

        return $this;
    }
}
