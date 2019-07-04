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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Storage\Sql;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompleteness;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\GetPublishedProductCompletenesses;
use Doctrine\DBAL\Connection;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class SqlGetPublishedProductCompletenesses implements GetPublishedProductCompletenesses
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fromPublishedProductId(int $publishedProductId): PublishedProductCompletenessCollection
    {
        $sql = <<<SQL
SELECT 
       channel.code AS channel_code,
       locale.code AS locale_code,
       completeness.required_count AS required_count,
       JSON_ARRAYAGG(attribute.code) AS missing_attribute_codes
FROM pimee_workflow_published_product_completeness completeness
    INNER JOIN pim_catalog_channel channel ON completeness.channel_id = channel.id
    INNER JOIN pim_catalog_locale locale ON completeness.locale_id = locale.id
    LEFT JOIN pimee_workflow_published_product_completeness_missing_attribute missing_attributes on completeness.id = missing_attributes.completeness_id
    LEFT JOIN pim_catalog_attribute attribute ON attribute.id = missing_attributes.missing_attribute_id
WHERE completeness.product_id = :publishedProductId
GROUP BY completeness.required_count, channel.code, locale.code
SQL;
        $rows = $this->connection->executeQuery($sql, ['publishedProductId' => $publishedProductId])->fetchAll();

        return new PublishedProductCompletenessCollection($publishedProductId, array_map(
            function (array $row) use ($publishedProductId): PublishedProductCompleteness {
                return new PublishedProductCompleteness(
                    $row['channel_code'],
                    $row['locale_code'],
                    (int)$row['required_count'],
                    array_filter(json_decode($row['missing_attribute_codes']))
                );
            },
            $rows
        ));
    }
}
