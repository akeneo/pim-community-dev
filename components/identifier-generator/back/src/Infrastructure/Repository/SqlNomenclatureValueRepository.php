<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureValueRepository;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SqlNomenclatureValueRepository implements NomenclatureValueRepository
{
    public function __construct(
        private readonly Connection $connection
    ) {
    }

    public function set(string $familyCode, ?string $value): void
    {
        // TODO This method has to be //
        // It should accept a big array of family_codes => values
        // Don't hesitate to call a method to get familyIds from familyCodes

        $checkSql = <<<SQL
SELECT id FROM pim_catalog_family WHERE code=:family_code
SQL;
        $resultFamily = $this->connection->fetchOne($checkSql, ['family_code' => $familyCode]);
        $familyId = $resultFamily === false ? null : \intval($resultFamily);

        if (null === $value) {
            if ($familyId) {
                $sql = <<<SQL
DELETE FROM pim_catalog_identifier_generator_family_nomenclature 
WHERE family_id=:family_id;
SQL;
                $this->connection->executeStatement($sql, [
                    'family_id' => $familyId,
                ]);
            }
            // Else ; the DELETE CASCADE already dropped it.
        }
        else {
            if ($familyId) {
                $sql = <<<SQL
INSERT INTO pim_catalog_identifier_generator_family_nomenclature (family_id, value)
VALUES(:family_id, :value)
ON DUPLICATE KEY UPDATE value = :value
SQL;

                $this->connection->executeStatement($sql, [
                    'family_id' => $familyId,
                    'value' => $value,
                ]);
            }
            // Else ; The family disappears, do nothing.
        }
    }

    public function get(string $familyCode): ?string
    {
        // TODO this method should not exist.
        // We can keep it until CPM-943 is done.
        $sql = <<<SQL
SELECT value FROM pim_catalog_identifier_generator_family_nomenclature n
INNER JOIN pim_catalog_family f ON f.id = n.family_id
WHERE f.code = :family_code
SQL;
        $result = $this->connection->fetchOne($sql, [
            'family_code' => $familyCode
        ]);

        return $result === false ? null : $result;
    }
}
