<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\ConnectedApp;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ConnectedAppRepositoryInterface
{
    public function create(ConnectedApp $app): void;
    public function findOneById(string $appId): ?ConnectedApp;
    /**
     * @return ConnectedApp[]
     */
    public function findAll(): array;
}
