<?php

namespace Akeneo\Component\Batch\Item;

use Akeneo\Component\Batch\Step\ResourceAwareInterface;

/**
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface ResourceItemReaderInterface extends ItemReaderInterface, ResourceAwareInterface
{
}
