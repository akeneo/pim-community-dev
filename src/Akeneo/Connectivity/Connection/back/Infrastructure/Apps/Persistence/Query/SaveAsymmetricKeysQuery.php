<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence\Query;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\Query\SaveAsymmetricKeysQueryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SaveAsymmetricKeysQuery implements SaveAsymmetricKeysQueryInterface
{
    public const OPTION_CODE = 'OPENID_ASYMMETRIC_KEYS';

    public function __construct(private Connection $connection)
    {
    }

    public function execute(AsymmetricKeys $asymmetricKeys): void
    {
        $query = <<<SQL
            INSERT INTO pim_configuration (`code`,`values`)
            VALUES (:code, :asymmetricKeys)
            ON DUPLICATE KEY UPDATE `values`= :asymmetricKeys
            SQL;

        $this->connection->executeQuery($query, [
            'code' => self::OPTION_CODE,
            'asymmetricKeys' => $asymmetricKeys->normalize(),
        ], [
            'code' => Types::STRING,
            'asymmetricKeys' => Types::JSON,
        ]);
    }
}
