<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Repository;

use Akeneo\Connectivity\Connection\Domain\Apps\Model\App;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AppRepositoryInterface
{
    public function create(App $app): void;
    public function findOneById(string $appId): ?App;
}
