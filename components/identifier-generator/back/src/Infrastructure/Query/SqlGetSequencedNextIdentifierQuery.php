<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Query;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UnableToGenerateAutoNumberException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\GetNextIdentifierQuery;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlGetSequencedNextIdentifierQuery implements GetNextIdentifierQuery
{
    public function __construct(
        private readonly GetNextIdentifierQuery $getNextIdentifierQuery,
        private readonly Connection $connection,
    ) {
    }

    public function fromPrefix(
        IdentifierGenerator $identifierGenerator,
        string $prefix,
        int $numberMin,
    ): int {
        $lastAllocatedNumber = $this->getLastAllocatedNumber($identifierGenerator, $prefix);
        if (null === $lastAllocatedNumber) {
            $nextIdentifier = $this->getNextIdentifierQuery->fromPrefix($identifierGenerator, $prefix, $numberMin);
            $this->insertLastAllocatedNumber($identifierGenerator, $prefix, $nextIdentifier);

            return $nextIdentifier;
        }

        $newAllocatedNumber = ($lastAllocatedNumber < $numberMin) ? $numberMin : $lastAllocatedNumber + 1;
        $remainingRetries = 10;
        $isSuccessful = false;
        while (!$isSuccessful && $remainingRetries > 0) {
            $isSuccessful = $this->updateLastAllocatedNumber($identifierGenerator, $prefix, $newAllocatedNumber) === 1;
            if (!$isSuccessful) {
                $newAllocatedNumber = $newAllocatedNumber + 1;
                $remainingRetries--;
            }
        }

        if (!$isSuccessful) {
            throw new UnableToGenerateAutoNumberException($prefix, $identifierGenerator->target()->asString());
        }

        return $newAllocatedNumber;
    }

    private function getLastAllocatedNumber(IdentifierGenerator $identifierGenerator, string $prefix): ?int
    {
        $sql = <<<SQL
SELECT last_allocated_number
FROM pim_catalog_identifier_generator_sequence s
    INNER JOIN pim_catalog_attribute a ON a.id = s.attribute_id
WHERE a.code=:attribute_code
    AND identifier_generator_uuid=UUID_TO_BIN(:identifier_generator_uuid)
    AND prefix=:prefix
SQL;

        $result = $this->connection->fetchOne($sql, [
            'attribute_code' => $identifierGenerator->target()->asString(),
            'identifier_generator_uuid' => $identifierGenerator->id()->asString(),
            'prefix' => $prefix,
        ]);

        return ($result === false) ? null : \intval($result);
    }

    private function insertLastAllocatedNumber(IdentifierGenerator $identifierGenerator, string $prefix, int $nextIdentifier): void
    {
        $sql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_sequence (attribute_id, identifier_generator_uuid, prefix, last_allocated_number)
VALUES (
        (SELECT id FROM pim_catalog_attribute WHERE code=:attribute_code),
        UUID_TO_BIN(:identifier_generator_uuid),
        :prefix,
        :number
    )
SQL;

        $this->connection->executeQuery($sql, [
            'attribute_code' => $identifierGenerator->target()->asString(),
            'identifier_generator_uuid' => $identifierGenerator->id()->asString(),
            'prefix' => $prefix,
            'number' => $nextIdentifier,
        ]);
    }

    private function updateLastAllocatedNumber(
        IdentifierGenerator $identifierGenerator,
        string $prefix,
        int $newAllocatedNumber
    ): int {
        $sql = <<<SQL
UPDATE pim_catalog_identifier_generator_sequence 
SET last_allocated_number=:number
WHERE attribute_id=(SELECT id FROM pim_catalog_attribute WHERE code=:attribute_code)
    AND identifier_generator_uuid=UUID_TO_BIN(:identifier_generator_uuid)
    AND prefix=:prefix
    AND last_allocated_number=:last_allocated_number
SQL;

        return \intval($this->connection->executeStatement($sql, [
            'attribute_code' => $identifierGenerator->target()->asString(),
            'identifier_generator_uuid' => $identifierGenerator->id()->asString(),
            'prefix' => $prefix,
            'number' => $newAllocatedNumber,
            'last_allocated_number' => $newAllocatedNumber - 1,
        ]));
    }
}
