<?php

namespace Pim\Bundle\UserBundle\Event;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * User event
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UserFormBuilderEvent extends GenericEvent
{
    /** @var string Event triggered after a form build */
    const POST_BUILD = 'pim_user.form.user.post_build';
}
