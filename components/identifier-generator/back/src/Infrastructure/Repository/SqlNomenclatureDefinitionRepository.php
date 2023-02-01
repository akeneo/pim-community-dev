<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureDefinitionRepository;
use Doctrine\DBAL\Connection;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlNomenclatureDefinitionRepository implements NomenclatureDefinitionRepository
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    public function get(string $propertyCode): ?NomenclatureDefinition
    {
        $sql = <<<SQL
SELECT definition
FROM pim_catalog_identifier_generator_nomenclature_definition
WHERE property_code=:property_code
SQL;
        $definition = $this->connection->fetchOne($sql, [
            'property_code' => $propertyCode,
        ]);

        if (false === $definition) {
            return null;
        }
        Assert::string($definition);

        $jsonResult = \json_decode($definition, true);
        Assert::isArray($jsonResult, sprintf('Invalid JSON: "%s"', $definition));

        return $this->fromNormalize($jsonResult);
    }

    public function update(string $propertyCode, NomenclatureDefinition $nomenclatureDefinition): void
    {
        $sql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_nomenclature_definition (property_code, definition)
VALUES(:property_code, :definition)
ON DUPLICATE KEY UPDATE definition = :definition
SQL;

        $this->connection->executeStatement($sql, [
            'property_code' => $propertyCode,
            'definition' => \json_encode($nomenclatureDefinition->normalize()),
        ]);
    }

    /**
     * @param array{
     *     operator?: string,
     *     value?: int,
     *     generate_if_empty?: bool
     * } $jsonResult
     */
    private function fromNormalize(array $jsonResult): NomenclatureDefinition
    {
        return new NomenclatureDefinition(
            $jsonResult['operator'] ?? null,
            $jsonResult['value'] ?? null,
            $jsonResult['generate_if_empty'] ?? null,
        );
    }
}
