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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Persistence\Query\Doctrine;

use Akeneo\Pim\Automation\SuggestData\Domain\Subscription\Query\EmptySuggestedDataAndMissingMappingQueryInterface;
use Doctrine\DBAL\Connection;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class EmptySuggestedDataAndMissingMappingQuery implements EmptySuggestedDataAndMissingMappingQueryInterface
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
    public function execute(string $familyCode): void
    {
        $query = <<<SQL
UPDATE pim_suggest_data_product_subscription s
INNER JOIN pim_catalog_product p ON p.id = s.product_id
INNER JOIN pim_catalog_family f ON f.id = p.family_id
SET s.raw_suggested_data = NULL, s.misses_mapping = NULL
WHERE s.raw_suggested_data IS NOT NULL
AND f.code = :familyCode;
SQL;
        $this->connection->executeQuery($query, ['familyCode' => $familyCode]);
    }
}
