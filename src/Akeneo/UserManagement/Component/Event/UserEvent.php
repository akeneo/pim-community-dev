<?php

namespace Akeneo\UserManagement\Component\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * User event
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserEvent extends GenericEvent
{
    /** @var string Event triggered after a user post update */
    const POST_UPDATE = 'pim_user.update.post';
}
