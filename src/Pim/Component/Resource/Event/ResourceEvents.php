<?php

namespace Pim\Component\Resource\Event;

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

    const PRE_BULK_SAVE = 'pre_bulk_save';
    const POST_BULK_SAVE = 'post_bulk_save';
    const PRE_BULK_CREATE = 'pre_bulk_create';
    const POST_BULK_CREATE = 'post_bulk_create';
    const PRE_BULK_UPDATE = 'pre_bulk_update';
    const POST_BULK_UPDATE = 'post_bulk_update';
    const PRE_BULK_DELETE = 'pre_bulk_delete';
    const POST_BULK_DELETE = 'post_bulk_delete';
}
