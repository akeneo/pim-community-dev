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
    /** @var array */
    protected $attributeIds = [];

    /**
     * Set attribute ids
     *
     * @param array $attributeIds
     */
    public function setAttributeIds(array $attributeIds)
    {
        $this->attributeIds = $attributeIds;
    }

    /**
     * Get attribute ids
     *
     * @return array
     */
    public function getAttributeIds()
    {
        return $this->attributeIds;
    }
}
