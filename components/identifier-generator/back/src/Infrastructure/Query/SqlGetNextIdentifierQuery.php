<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Query;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\GetNextIdentifierQuery;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetNextIdentifierQuery implements GetNextIdentifierQuery
{
    public function __construct(
        private Connection $connection
    ) {
    }

    public function fromPrefix(
        IdentifierGenerator $identifierGenerator,
        string $prefix,
        int $numberMin,
    ): int {
        $sql = <<<SQL
SELECT MAX(number) FROM pim_catalog_identifier_generator_prefixes p 
INNER JOIN pim_catalog_attribute a ON a.id = p.attribute_id
WHERE p.prefix = :prefix AND a.code = :code
SQL;

        $result = $this->connection->executeQuery(
            $sql,
            [
                'code' => $identifierGenerator->target()->asString(),
                'prefix' => $prefix,
            ],
            [
                'code' => \PDO::PARAM_STR,
                'prefix' => \PDO::PARAM_STR,
            ]
        )->fetchOne();

        Assert::nullOrString($result);
        if (null === $result || $result === '') {
            return $numberMin;
        }

        $result = ((int) $result) + 1;

        return ($result < $numberMin) ? $numberMin : $result;
    }
}
