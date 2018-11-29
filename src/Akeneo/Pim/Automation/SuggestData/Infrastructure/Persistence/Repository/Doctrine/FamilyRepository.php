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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Repository\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\Family;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Model\Read\FamilyCollection;
use Akeneo\Pim\Automation\SuggestData\Domain\AttributeMapping\Repository\FamilyRepositoryInterface;
use Doctrine\DBAL\Connection;

/**
 * Doctrine implementation of the repository of the attribute mapping read model "Family".
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
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
    public function findBySearch(int $page, int $limit, ?string $search, array $familyCodes): FamilyCollection
    {
        $query = <<<SQL
SELECT
    f.code, 
    JSON_OBJECTAGG(ft.locale, ft.label) as labels, 
    SUM(s.misses_mapping) as misses_mapping 
FROM pim_suggest_data_product_subscription s
INNER JOIN pim_catalog_product p ON s.product_id = p.id
INNER JOIN pim_catalog_family f ON p.family_id = f.id
INNER JOIN pim_catalog_family_translation ft ON f.id = ft.foreign_key
WHERE f.code IN (:familyCodes) AND (f.code like :search OR ft.label like :search)
GROUP BY f.code ORDER BY f.id LIMIT :limit OFFSET :offset;
SQL;

        $queryParameters = [
            'familyCodes' => $familyCodes,
            'search' => '%' . $search . '%',
            'limit' => $limit,
            'offset' => $limit * ($page - 1),
        ];
        $types = [
            'familyCodes' => Connection::PARAM_STR_ARRAY,
            'search' => \PDO::PARAM_STR,
            'limit' => \PDO::PARAM_INT,
            'offset' => \PDO::PARAM_INT,
        ];

        $statement = $this->connection->executeQuery($query, $queryParameters, $types);

        $families = $statement->fetchAll();

        $familyCollection = new FamilyCollection();
        foreach ($families as $family) {
            $familyCollection->add(
                new Family(
                    $family['code'],
                    json_decode($family['labels'], true),
                    (bool) $family['misses_mapping'] ? Family::MAPPING_PENDING : Family::MAPPING_FULL
                )
            );
        }

        return $familyCollection;
    }
}
