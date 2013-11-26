<?php

namespace Context\Page\VariantGroup;

use Context\Page\ProductGroup\Edit as GroupEdit;

/**
 * Variant group edit page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends GroupEdit
{
    /**
     * @var string
     */
    protected $path = '/enrich/variant-group/{id}/edit';
}
