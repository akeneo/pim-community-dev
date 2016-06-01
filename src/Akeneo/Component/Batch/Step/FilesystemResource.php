<?php

namespace Akeneo\Component\Batch\Step;

/**
 * Simple resource implementation of the local file system.
 * A resource can be used by the steps as inputs or outputs.
 *
 * Typically, the item step can:
 *    - use a resource as input of a reader see {@link \Akeneo\Component\Batch\Item\ResourceItemReaderInterface}
 *    - use a resource as output of a writer see {@link \Akeneo\Component\Batch\Item\ResourceItemWriterInterface}
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class FilesystemResource extends \SplFileInfo
{
}
