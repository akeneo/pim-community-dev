<?php

namespace Pim\Bundle\ImportExportBundle\Item;

/**
 * Object representing a context for an {@link ItemStream}. It is a thin wrapper
 * for a map that allows optionally for type safety on reads. It also allows for
 * dirty checking by setting a 'dirty' flag whenever any put is called.
 *
 * Note that putting <code>null</code> value is equivalent to removing the entry
 * for the given key.
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class ExecutionContext
{
}
