<?php

namespace Akeneo\Component\StorageUtils\Repository;

use Akeneo\Component\StorageUtils\Cache\CacheClearerInterface;

/**
 * Interface for repositories of cached unique code objects
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal
 */
interface CachedObjectRepositoryInterface extends IdentifiableObjectRepositoryInterface, CacheClearerInterface
{
}
