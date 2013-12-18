<?php

namespace Pim\Bundle\CatalogBundle\Entity;

/**
 * Interface for entities with an unique code
 * 
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface WithUniqueCodeInterface
{
    /**
     * Returns the unique code for the entity
     * 
     * @return string
     */
    public function getUniqueCode();
}
