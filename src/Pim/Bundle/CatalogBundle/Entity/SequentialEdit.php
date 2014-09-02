<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
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
     * @var array
     */
    protected $productset;
    
    /** 
     * @var String $id 
     */
    protected $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->productset = new ArrayCollection();
    }

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
     * Get productset
     *
     * @return array
     */
    public function getProductset()
    {
        return $this->productset;
    }

    /**
     * Set productset
     *
     * @param array productset
     *
     * @return SequentialEdit
     */
    public function setProductset(array $productset)
    {
        $this->productset = $productset;

        return $this;
    }
}
