<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics;

use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAverageMaxNumberOfAttributesPerReferenceEntity
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    public function fetch(): AverageMaxVolumes
    {
        $sql = <<<SQL
SELECT 
	MAX(number_of_attributes_per_reference_entity) as max,
	CEIL(AVG(number_of_attributes_per_reference_entity)) as average
FROM (
	SELECT reference_entity.identifier, COUNT(code) as number_of_attributes_per_reference_entity
	FROM akeneo_reference_entity_reference_entity reference_entity
		LEFT JOIN akeneo_reference_entity_attribute attribute
		ON reference_entity.identifier = attribute.reference_entity_identifier
	GROUP BY reference_entity.identifier
) as rec;
SQL;
        $result = $this->sqlConnection->query($sql)->fetch();

        return new AverageMaxVolumes(
            (int) $result['max'],
            (int) $result['average']
        );
    }
}
