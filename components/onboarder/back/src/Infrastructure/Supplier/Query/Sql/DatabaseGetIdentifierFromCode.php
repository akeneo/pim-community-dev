<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Query\Sql;

use Akeneo\OnboarderSerenity\Domain\Supplier\Read\GetIdentifierFromCode;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Code;
use Doctrine\DBAL\Connection;

final class DatabaseGetIdentifierFromCode implements GetIdentifierFromCode
{
    public function __construct(private Connection $connection)
    {
    }

    public function __invoke(Code $code): ?string
    {
        $identifier = $this->connection->executeQuery(
            <<<SQL
                SELECT identifier
                FROM `akeneo_onboarder_serenity_supplier`
                WHERE code = :code
            SQL
            ,
            [
                'code' => $code,
            ],
        )->fetchOne();

        return false !== $identifier ? $identifier : null;
    }
}
