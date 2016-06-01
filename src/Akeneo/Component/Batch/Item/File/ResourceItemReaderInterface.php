<?php

namespace Akeneo\Component\Batch\Item\File;

use Akeneo\Component\Batch\Item\ItemReaderInterface;
use Akeneo\Component\Batch\Step\ResourceAwareInterface;

/**
 * Read items from a file resource.
 *
 * Inspired by org.springframework.batch.item.file ResourcesItemReader
 *
 * See {@link \Akeneo\Component\Batch\Item\ItemReaderInterface}
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface ResourceItemReaderInterface extends ItemReaderInterface, ResourceAwareInterface
{
}
