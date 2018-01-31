<?php

namespace Context\Page\User;

use Context\Page\Base\Form;

/**
 * User edit page
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Show extends Form
{
    /** @var string */
    protected $path = '#/user/view/{id}';
}
