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

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\Projection\PublishedProductCompletenessCollection;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Query\SavePublishedProductCompletenesses;
use Doctrine\DBAL\Connection;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SqlSavePublishedProductCompletenesses implements SavePublishedProductCompletenesses
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function save(PublishedProductCompletenessCollection $completenesses): void
    {
        $this->connection->beginTransaction();

        $publishedProductId = $completenesses->publishedProductId();
        $this->connection->executeQuery($this->getDeleteQuery(), ['publishedProductId' => $publishedProductId]);

        foreach ($completenesses as $completeness) {
            $this->connection->executeQuery(
                $this->getInsertCompletenessQuery(),
                [
                    'publishedProductId' => $publishedProductId,
                    'ratio' => $completeness->ratio(),
                    'missingCount' => count($completeness->missingAttributeCodes()),
                    'requiredCount' => $completeness->requiredCount(),
                    'localeCode' => $completeness->localeCode(),
                    'channelCode' => $completeness->channelCode(),
                ]
            );
            $completenessId = $this->connection->lastInsertId();
            $this->connection->executeUpdate(
                $this->getInsertMissingAttributesQuery(),
                [
                    'completenessId' => $completenessId,
                    'attributeCodes' => $completeness->missingAttributeCodes(),
                ],
                [
                    'attributeCodes' => Connection::PARAM_STR_ARRAY,
                ]
            );
        }

        $this->connection->commit();
    }

    private function getDeleteQuery(): string
    {
        return <<<SQL
DELETE FROM pimee_workflow_published_product_completeness
WHERE product_id = :publishedProductId
SQL;
    }

    private function getInsertCompletenessQuery(): string
    {
        return <<<SQL
INSERT INTO pimee_workflow_published_product_completeness(locale_id, channel_id, product_id, ratio, missing_count, required_count)
SELECT locale.id, channel.id, :publishedProductId, :ratio, :missingCount, :requiredCount  
FROM pim_catalog_locale locale,
     pim_catalog_channel channel
WHERE locale.code = :localeCode
  AND channel.code = :channelCode
SQL;
    }

    private function getInsertMissingAttributesQuery(): string
    {
        return <<<SQL
INSERT INTO pimee_workflow_published_product_completeness_missing_attribute(completeness_id, missing_attribute_id)
SELECT :completenessId, attribute.id
FROM pim_catalog_attribute attribute
WHERE attribute.code IN (:attributeCodes)
SQL;
    }
}
