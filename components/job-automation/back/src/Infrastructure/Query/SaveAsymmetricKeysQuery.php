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

use Akeneo\Platform\JobAutomation\Domain\Model\AsymmetricKeys;
use Akeneo\Platform\JobAutomation\Domain\ClockInterface;
use Akeneo\Platform\JobAutomation\Domain\Query\SaveAsymmetricKeysQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

final class SaveAsymmetricKeysQuery implements SaveAsymmetricKeysQueryInterface
{
    public const OPTION_CODE = 'SFTP_ASYMMETRIC_KEYS';

    public function __construct(
        private Connection $connection,
        private ClockInterface $clock,
    ) {
    }

    public function execute(AsymmetricKeys $asymmetricKeys): void
    {
        $query = <<<SQL
            INSERT INTO pim_configuration (`code`,`values`)
            VALUES (:code, :asymmetricKeys)
            ON DUPLICATE KEY UPDATE `values`= :asymmetricKeys
            SQL;

        $updatedAt = $this->clock->now()->format(\DateTimeInterface::ATOM);

        $this->connection->executeQuery($query, [
            'code' => self::OPTION_CODE,
            'asymmetricKeys' => \array_merge($asymmetricKeys->normalize(), ['updated_at' => $updatedAt]),
        ], [
            'code' => Types::STRING,
            'asymmetricKeys' => Types::JSON,
        ]);
    }
}
