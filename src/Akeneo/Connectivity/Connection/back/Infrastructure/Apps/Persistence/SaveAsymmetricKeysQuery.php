<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\SaveAsymmetricKeysQueryInterface;
use Akeneo\Connectivity\Connection\Domain\ClockInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SaveAsymmetricKeysQuery implements SaveAsymmetricKeysQueryInterface
{
    public const OPTION_CODE = 'OPENID_ASYMMETRIC_KEYS';

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
