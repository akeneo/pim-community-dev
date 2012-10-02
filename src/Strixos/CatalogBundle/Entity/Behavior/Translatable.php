<?php

namespace Strixos\CatalogBundle\Entity\Behavior;

/**
 * 
 * @author Romain Monceau @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * Strixos\CatalogBundle\Entity\Behavior\Translatable
 */
interface Translatable
{
    /**
     * Return current locale
     * @return string
     */
    public function getCurrentLocale();
    
    /**
     * Switch to another locale
     * @param string $code
     */
    public function switchCurrentLocale($code);
}