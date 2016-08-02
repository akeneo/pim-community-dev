<?php

namespace Akeneo\Component\StorageUtils\Cache;

/**
 * Clears the cache
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @api
 */
interface CacheClearerInterface
{
    /**
     * Clears the internal cache
     *
     * @return CacheClearerInterface
     *
     * @api
     */
    public function clear();
}
