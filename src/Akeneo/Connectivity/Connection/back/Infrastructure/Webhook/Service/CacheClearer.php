<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache\LRUCachedGetAttributes;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;
use Akeneo\Tool\Component\StorageUtils\Cache\CachedQueriesClearerInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CacheClearer implements CacheClearerInterface
{
    private UnitOfWorkAndRepositoriesClearer $unitOfWorkAndRepositoriesClearer;
    private CachedQueriesClearerInterface $cachedQueriesClearer;

    public function __construct(
        UnitOfWorkAndRepositoriesClearer $unitOfWorkAndRepositoriesClearer,
        CachedQueriesClearerInterface $cachedQueriesClearer
    ) {
        $this->unitOfWorkAndRepositoriesClearer = $unitOfWorkAndRepositoriesClearer;
        $this->cachedQueriesClearer = $cachedQueriesClearer;
    }

    public function clear(): void
    {
        $this->unitOfWorkAndRepositoriesClearer->clear();
        $this->cachedQueriesClearer->clear();
    }
}
