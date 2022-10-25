<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Domain\Exception\AsymmetricKeyNotFoundException;
use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use Akeneo\Platform\JobAutomation\Domain\Query\GetAsymmetricKeysQueryInterface;
use Doctrine\DBAL\Connection;

final class GetAsymmetricKeysQuery implements GetAsymmetricKeysQueryInterface
{
    public function __construct(
        private Connection $connection,
    ) {
    }

    public function execute(string $code): AsymmetricKeys
    {
        $query = <<<SQL
            SELECT `values` FROM pim_configuration
            WHERE code = :code
        SQL;

        $result = $this->connection->fetchOne($query, ['code' => $code]);

        if (!$result) {
            throw new AsymmetricKeyNotFoundException($code);
        }

        $keys = \json_decode($result, true);

        return AsymmetricKeys::create($keys[AsymmetricKeys::PUBLIC_KEY], $keys[AsymmetricKeys::PRIVATE_KEY]);
    }
}
