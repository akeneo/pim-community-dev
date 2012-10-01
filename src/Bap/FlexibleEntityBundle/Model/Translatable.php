<?php
namespace Bap\FlexibleEntityBundle\Model;

/**
 * Interface to implements to make an entity translatable
 *
 * @author Nicolas Dupont @ Strixos
 * @copyright  Copyright (c) 2012 Strixos SAS (http://www.strixos.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Translatable
{
    /**
    * Configure the entity with selected locale
    */
    public function switchCurrentLocale(string $code);

    /**
     * Get the used locale
     * @return string
     */
    public function getCurrentLocale();
}
