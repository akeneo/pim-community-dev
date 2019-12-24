<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Settings\Persistence\Repository;

use Akeneo\Apps\Domain\Settings\Model\Write\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ConnectionRepository
{
    public function findOneByCode(string $code): ?Connection;

    public function create(Connection $connection): void;

    public function update(Connection $connection): void;

    public function delete(Connection $connection): void;
}
