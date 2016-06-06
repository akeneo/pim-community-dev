<?php

namespace Akeneo\Component\Batch\Item\File;

use Akeneo\Component\Batch\Item\ItemWriterInterface;
use Akeneo\Component\Batch\Step\ResourceAwareInterface;

/**
 * Write items to a file resource.
 *
 * See {@link \Akeneo\Component\Batch\Item\ItemWriterInterface}
 * See {@link \Akeneo\Component\Batch\Item\ResourceItemReaderInterface}
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface ResourceItemWriterInterface extends ItemWriterInterface, ResourceAwareInterface
{
}
