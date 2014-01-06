<?php

namespace Pim\Bundle\CatalogBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Available attributes model
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AvailableAttributes
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
