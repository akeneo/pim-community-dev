<?php

namespace Context\Page\UserRole;

use Context\Page\Base\Form;

/**
 * User role edit page
 *
 * @author    Jean-Marie Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Form
{
    /** @var string */
    protected $path = '/user/role/update/{id}';
}
