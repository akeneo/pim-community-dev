<?php

namespace Akeneo\Tool\Component\StorageUtils\Cache;

/**
 * Clears entities
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface EntityManagerClearerInterface
{
    /**
     * Clears the internal cache
     *
     * @return EntityManagerClearerInterface
     */
    public function clear();
}
