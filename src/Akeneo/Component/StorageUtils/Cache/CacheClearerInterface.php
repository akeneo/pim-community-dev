<?php

namespace Akeneo\Component\StorageUtils\Cache;

/**
 * Clears the cache
 *
 * TODO This interface is not a "cache clearer", it's an entity manager clearer, was not renamed to avoid BC.
 * This has to be renamed on merge to master.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CacheClearerInterface
{
    /**
     * Clears the internal cache
     *
     * @return CacheClearerInterface
     */
    public function clear();
}
