<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Persistence;

use Akeneo\Connectivity\Connection\Domain\Apps\DTO\AsymmetricKeys;
use Akeneo\Connectivity\Connection\Domain\Apps\Exception\OpenIdKeysNotFoundException;
use Akeneo\Connectivity\Connection\Domain\Apps\Persistence\GetAsymmetricKeysQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAsymmetricKeysQuery implements GetAsymmetricKeysQueryInterface
{
    public function __construct(private Connection $connection)
    {
    }

    public function execute(): AsymmetricKeys
    {
        $query = <<<SQL
        SELECT `values` FROM pim_configuration
        WHERE code = :code
        SQL;

        $result = $this->connection->fetchOne($query, ['code' => SaveAsymmetricKeysQuery::OPTION_CODE]);

        if (!$result) {
            throw new OpenIdKeysNotFoundException();
        }

        $keys = \json_decode($result, true, 512, JSON_THROW_ON_ERROR);

        return AsymmetricKeys::create($keys[AsymmetricKeys::PUBLIC_KEY], $keys[AsymmetricKeys::PRIVATE_KEY]);
    }
}
