<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\StorageUtils\Cache;

/**
 * Least Recently Used Cache
 *
 * A fixed sized cache that removes the element used last when it reaches its
 * size limit.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CacheClearerInterface
{
    public function clearCache(): void;
}
