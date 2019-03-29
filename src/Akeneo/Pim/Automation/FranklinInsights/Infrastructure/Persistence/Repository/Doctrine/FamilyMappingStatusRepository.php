<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatus;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Model\Read\FamilyMappingStatusCollection;
use Akeneo\Pim\Automation\FranklinInsights\Domain\AttributeMapping\Repository\FamilyMappingStatusRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\Family;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\FamilyCode;
use Doctrine\DBAL\Connection;

/**
 * Doctrine implementation of the repository of the attribute mapping read model "Family".
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class FamilyMappingStatusRepository implements FamilyMappingStatusRepositoryInterface
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
    public function findBySearch(int $page, int $limit, ?string $search): FamilyMappingStatusCollection
    {
        $query = <<<SQL
SELECT
    family.code,
    ANY_VALUE(JSON_OBJECTAGG(IFNULL(fam_label.locale, 0), fam_label.label)) as labels,
    SUM(subscription.misses_mapping) as misses_mapping
FROM pim_catalog_family family
INNER JOIN pim_catalog_product product ON product.family_id = family.id
INNER JOIN pimee_franklin_insights_subscription subscription ON subscription.product_id = product.id
LEFT JOIN pim_catalog_family_translation fam_label ON family.id = fam_label.foreign_key
WHERE family.code like :search OR fam_label.label like :search
GROUP BY family.code ORDER BY family.id LIMIT :limit OFFSET :offset;
SQL;

        $queryParameters = [
            'search' => '%' . $search . '%',
            'limit' => $limit,
            'offset' => $limit * ($page - 1),
        ];
        $types = [
            'search' => \PDO::PARAM_STR,
            'limit' => \PDO::PARAM_INT,
            'offset' => \PDO::PARAM_INT,
        ];

        $statement = $this->connection->executeQuery($query, $queryParameters, $types);

        return $this->hydrate($statement->fetchAll());
    }

    /**
     * Hydrates family read models from SQL result.
     *
     * @param array $familyRows
     *
     * @return FamilyMappingStatusCollection
     */
    private function hydrate(array $familyRows): FamilyMappingStatusCollection
    {
        $familyCollection = new FamilyMappingStatusCollection();
        foreach ($familyRows as $familyRow) {
            $familyCode = new FamilyCode($familyRow['code']);
            $familyLabels = json_decode($familyRow['labels'], true);

            $familyCollection->add(
                new FamilyMappingStatus(
                    new Family($familyCode, $familyLabels),
                    (bool) $familyRow['misses_mapping'] ? FamilyMappingStatus::MAPPING_PENDING : FamilyMappingStatus::MAPPING_FULL
                )
            );
        }
        return $familyCollection;
    }
}
