<?php

namespace Pim\Bundle\ProductBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AvailableProductAttributes
{
    protected $attributes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->attributes = new ArrayCollection();
    }

    /**
     * Set attributes to add
     *
     * @param ArrayCollection $attributes The attributes to add
     *
     * @return null
     */
    public function setAttributes(ArrayCollection $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * Get the attributes to add
     *
     * @return ArrayCollection
     */
    public function getAttributes()
    {
        return $this->attributes;
    }
}
