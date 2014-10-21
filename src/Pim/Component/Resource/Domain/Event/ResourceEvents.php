<?php

namespace Pim\Component\Resource\Domain\Event;

/**
 * Resource events types that can be dispatched.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ResourceEvents
{
    const PRE_SAVE = 'pre_save';
    const POST_SAVE = 'post_save';
    const PRE_CREATE = 'pre_create';
    const POST_CREATE = 'post_create';
    const PRE_UPDATE = 'pre_update';
    const POST_UPDATE = 'post_update';
    const PRE_DELETE = 'pre_delete';
    const POST_DELETE = 'post_delete';
}
