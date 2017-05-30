<?php

namespace Context\Page\Channel;

/**
 * Channel edit page
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Edit extends Creation
{
    /**
     * @var string
     */
    protected $path = '#/configuration/channel/{code}/edit';
}
