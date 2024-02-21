<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Query;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Query\ReorderIdentifierGenerators;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class SqlReorderIdentifierGenerators implements ReorderIdentifierGenerators
{
    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * {inheritdoc}
     */
    public function byCodes(array $codes): void
    {
        $formerCodes = $this->connection->executeQuery(
            'SELECT code FROM pim_catalog_identifier_generator ORDER BY sort_order ASC;'
        )->fetchFirstColumn();

        $newCodes = \array_map(static fn (IdentifierGeneratorCode $code): string => $code->asString(), $codes);
        $newCodes = \array_filter(
            $newCodes,
            static fn (string $code): bool => \in_array($code, $formerCodes)
        );
        $nonMappedCodes = \array_diff($formerCodes, $newCodes);
        $newCodes = \array_values(\array_merge($newCodes, $nonMappedCodes));

        $this->connection->executeStatement(
            <<<SQL
            UPDATE pim_catalog_identifier_generator
            SET sort_order = FIELD(code, :codes) - 1;
            SQL,
            ['codes' => $newCodes],
            ['codes' => Connection::PARAM_STR_ARRAY]
        );
    }
}
