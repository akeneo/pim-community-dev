<?php

namespace Context\Page\User;

use Context\Page\Base\Base;
use Context\Traits\ClosestTrait;

/**
 * "My Account" page
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Profile extends Base
{
    use ClosestTrait;

    /** @var string */
    protected $path = '#/user/profile/view';
}
