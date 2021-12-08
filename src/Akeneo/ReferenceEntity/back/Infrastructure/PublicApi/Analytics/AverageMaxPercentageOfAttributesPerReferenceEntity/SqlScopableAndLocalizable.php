<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxPercentageOfAttributesPerReferenceEntity;

use Akeneo\ReferenceEntity\Infrastructure\PublicApi\Analytics\AverageMaxVolumes;
use Doctrine\DBAL\Connection;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlScopableAndLocalizable
{
    public function __construct(
        private Connection $sqlConnection
    ) {
    }

    public function fetch(): AverageMaxVolumes
    {
        $sql = <<<SQL
SELECT 
	MAX(number_of_localizable_only_attributes_per_reference_entity * 100 / number_of_attributes) as max,
	CEIL(AVG(number_of_localizable_only_attributes_per_reference_entity * 100 / number_of_attributes)) as average
FROM (
	SELECT reference_entity_identifier,
	       SUM(value_per_locale = 1 AND value_per_channel = 1) as number_of_localizable_only_attributes_per_reference_entity,
	       COUNT(*) as number_of_attributes
	FROM akeneo_reference_entity_attribute 
	GROUP BY reference_entity_identifier
) AS rec;
SQL;
        $result = $this->sqlConnection->query($sql)->fetch();

        return new AverageMaxVolumes(
            (int) $result['max'],
            (int) $result['average']
        );
    }
}
