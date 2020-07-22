<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Query\Structure;

use Akeneo\Pim\Automation\DataQualityInsights\Application\Clock;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\UpdatedFamily;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\Query\Structure\GetFamiliesWithUpdatedAttributesListQueryInterface;
use Doctrine\DBAL\Connection;

final class GetFamiliesWithUpdatedAttributesListQuery implements GetFamiliesWithUpdatedAttributesListQueryInterface
{
    /** @var Connection */
    private $dbConnection;

    /** @var Clock */
    private $clock;

    /** @var string */
    private $familyClass;

    public function __construct(Connection $dbConnection, Clock $clock, string $familyClass)
    {
        $this->dbConnection = $dbConnection;
        $this->clock = $clock;
        $this->familyClass = $familyClass;
    }

    public function updatedSince(\DateTimeImmutable $evaluatedSince): array
    {
        $query = <<<SQL
SELECT resource_id AS id, MAX(logged_at) AS updated_at
FROM pim_versioning_version
WHERE resource_name = :familyClass
    AND logged_at >= :evaluatedSince
    AND changeset LIKE '%%s:10:"attributes";a:2:%%'
GROUP BY resource_id;
SQL;

        $families = $this->dbConnection->executeQuery($query, [
            'familyClass' => $this->familyClass,
            'evaluatedSince' => $evaluatedSince->format(Clock::TIME_FORMAT),
        ])->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(function ($result) {
            return new UpdatedFamily(intval($result['id']), $this->clock->fromString($result['updated_at']));
        }, $families);
    }
}
