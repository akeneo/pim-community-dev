<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Repository\FamilyRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;

final class FamilyRepository implements FamilyRepositoryInterface
{
    /** @var Connection */
    private $connection;

    /**
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function exist(FamilyCode $familyCode): bool
    {
        $query = <<<SQL
SELECT EXISTS(
  SELECT 1 FROM pim_catalog_family WHERE code = :family_code
) as family_exist
SQL;
        $statement = $this->connection->executeQuery(
            $query,
            ['family_code' => (string) $familyCode],
            ['family_code' => \PDO::PARAM_STR]

        );

        return (bool) $statement->fetchColumn(0);
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier(FamilyCode $familyCode): ?Family
    {
        $query = <<<SQL
SELECT
    family.code,
    ANY_VALUE(JSON_OBJECTAGG(IFNULL(fam_label.locale, 0), fam_label.label)) as labels
FROM pim_catalog_family family
LEFT JOIN akeneo_pim.pim_catalog_family_translation fam_label ON family.id = fam_label.foreign_key
WHERE family.code = :family_code
GROUP BY family.code
SQL;

        $statement = $this->connection->executeQuery(
            $query,
            ['family_code' => (string) $familyCode],
            ['family_code' => \PDO::PARAM_STR]

        );

        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        return empty($result) ? null : new Family(new FamilyCode($result['code']), json_decode($result['labels'], true));
    }
}
