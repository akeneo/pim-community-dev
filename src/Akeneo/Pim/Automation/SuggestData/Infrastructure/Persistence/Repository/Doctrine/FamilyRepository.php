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
use Akeneo\Tool\Component\StorageUtils\Repository\SearchableRepositoryInterface;
use Doctrine\DBAL\Connection;

/**
 * Doctrine implementation of the repository of the attribute mapping read model "Family".
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
final class FamilyRepository implements FamilyRepositoryInterface
{
    /** @var SearchableRepositoryInterface */
    private $familyRepository;

    /** @var Connection */
    private $connection;

    /**
     * @param SearchableRepositoryInterface $familyRepository
     * @param Connection $connection
     */
    public function __construct(
        SearchableRepositoryInterface $familyRepository,
        Connection $connection
    ) {
        $this->familyRepository = $familyRepository;
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function findBySearch(int $page, int $limit, ?string $search, array $identifiers): FamilyCollection
    {
        $families = $this->familyRepository->findBySearch($search, [
            'page' => $page,
            'limit' => $limit,
            'identifiers' => $identifiers,
        ]);

        $familyCodes = [];
        foreach ($families as $family) {
            $familyCodes[] = $family->getCode();
        }

        $query = <<<SQL
SELECT f.code, SUM(s.misses_mapping) as misses_mapping FROM pim_suggest_data_product_subscription s
INNER JOIN pim_catalog_product p ON s.product_id = p.id
INNER JOIN pim_catalog_family f ON p.family_id = f.id WHERE f.code IN (:familyCodes)
GROUP BY f.code;
SQL;

        $queryParameters = ['familyCodes' => $familyCodes];
        $types = ['familyCodes' => Connection::PARAM_STR_ARRAY];

        $statement = $this->connection->executeQuery($query, $queryParameters, $types);

        $results = $statement->fetchAll();

        $attributeStatusesByFamily = [];
        foreach ($results as $result) {
            $familyCode = $result['code'];
            $missesMapping = (bool) $result['misses_mapping'] ? Family::MAPPING_PENDING : Family::MAPPING_FULL;

            $attributeStatusesByFamily[$familyCode] = $missesMapping;
        }

        $familyCollection = new FamilyCollection();
        foreach ($families as $family) {
            if (array_key_exists($family->getCode(), $attributeStatusesByFamily)) {
                $labels = [];
                foreach ($family->getTranslations() as $translation) {
                    $labels[$translation->getLocale()] = $translation->getLabel();
                }
                $familyCollection->add(
                    new Family(
                        $family->getCode(),
                        $labels,
                        $attributeStatusesByFamily[$family->getCode()]
                    )
                );
            }
        }

        return $familyCollection;
    }
}
