<?php

namespace Pim\Component\Resource\Event;

use Pim\Component\Resource\ResourceSetInterface;

/**
 * Default bulk resource event interface
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ResourceBulkEventInterface
{
    /**
     * @return ResourceSetInterface
     */
    public function getResources();
}
