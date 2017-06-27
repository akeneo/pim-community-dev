<?php

namespace Context\Page\VariantGroup;

use Context\Page\ProductGroup\Creation as GroupCreation;

/**
 * Variant group creation page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends GroupCreation
{
    /** @var string */
    protected $path = '#/enrich/variant-group/create';

    /**
    * Find a validation tooltip containing a text
    *
    * @param string $text
    *
    * @return null|Element
    */
    public function findValidationTooltip($text)
    {
        return $this->spin(function () use ($text) {
            return $this->find(
             'css',
             sprintf(
                 '.validation-errors .error-message:contains("%s")',
                 $text
             )
         );
        }, sprintf('Cannot find error message "%s" in validation tooltip', $text));
    }
}
