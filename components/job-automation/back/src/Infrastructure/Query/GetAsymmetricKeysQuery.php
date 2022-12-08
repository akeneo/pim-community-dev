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

use Akeneo\Platform\JobAutomation\Domain\Exception\AsymmetricKeysNotFoundException;
use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use Akeneo\Platform\JobAutomation\Domain\Query\GetAsymmetricKeysQueryInterface;
use Doctrine\DBAL\Connection;

final class GetAsymmetricKeysQuery implements GetAsymmetricKeysQueryInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function execute(): AsymmetricKeys
    {
        $query = <<<SQL
            SELECT
                JSON_UNQUOTE(JSON_EXTRACT(`values`, '$.public_key')) as publicKey,
                JSON_UNQUOTE(JSON_EXTRACT(`values`, '$.private_key')) as privateKey
            FROM pim_configuration
            WHERE code = :code
        SQL;

        $result = $this->connection->fetchAssociative($query, ['code' => SaveAsymmetricKeysQuery::OPTION_CODE]);

        if (!$result) {
            throw new AsymmetricKeysNotFoundException();
        }

        return AsymmetricKeys::create($result['publicKey'], $result['privateKey']);
    }
}
