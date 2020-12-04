<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AnalyticsBundle\Query\ElasticsearchAndSql;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\Analytics\MediaCountQuery;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;

/**
 * This query count the number of media files or media images.
 *
 * It's not possible to use ES aggregation, as the code of a media is not indexed with a keyword.
 * The solution is to query the number of products for each field (a field = values.my_image.fr_FR.ecommerce).
 *
 * Example:
 * - query 1: count the number of products having the media file field "values.my_image.ecommerce.fr_FR" filled
 * - query 2: count the number of products having the media file field "values.my_image.ecommerce.en_US" filled
 * - query 3: count the number of products having the media file field "values.my_image.tablet.en_US" filled
 *
 * Queries are run in parallel for performance (= msearch). Then, add the result for each query = total number of files.
 *
 * Edge-cases:
 *
 * 1. Media code shared among several products/values
 *
 * In theory, a media file is not shared between products or between values of a same product.
 * However, it's technically possible to use the same media code for 2 different media attribute code, with the API for example.
 *
 * This implementation will count it as different media, even if the code of the media is the same.
 * For example, it will return 2 even if the same media code is used among 2 different products.
 *
 * 2. Media code in product model
 *
 * Product documents in ES inherit from the values of the product model. Therefore, we count the number of medias
 * in product and product models.
 *
 * However, if a product model does not have any children product but a media file, it does not count it.
 * It's normal, as we search only in the products.
 * This case should not happen very often as a product model without children product is useless.
 *
 * This is done on purpose and what we expect.
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaCount implements MediaCountQuery
{
    private Connection $connection;

    private Client $client;

    public function __construct(Connection $connection, Client $client)
    {
        $this->connection = $connection;
        $this->client = $client;
    }

    public function countFiles(): int
    {
        $fieldPaths = $this->fetchESFieldPathsForAttributesOfType('pim_catalog_file');

        return $this->countMediasFromES($fieldPaths);
    }

    public function countImages(): int
    {
        $fieldPaths = $this->fetchESFieldPathsForAttributesOfType('pim_catalog_image');

        return $this->countMediasFromES($fieldPaths);
    }

    private function fetchESFieldPathsForAttributesOfType(string $attributeType): array
    {
        $sql = <<<SQL
            WITH channel_locale AS (
                SELECT
                    channel.id AS channel_id,
                    channel.code AS channel_code,
                    locale.id AS locale_id,
                    locale.code AS locale_code
                FROM pim_catalog_channel channel
                JOIN pim_catalog_channel_locale pccl ON channel.id = pccl.channel_id
                JOIN pim_catalog_locale locale ON pccl.locale_id = locale.id
            )
            SELECT DISTINCT
                CONCAT(
                    'values.',
                    attribute.code,
                    '-media.',
                    IF(attribute.is_scopable, channel_locale.channel_code, '<all_channels>'),
                    '.',
                    IF(attribute.is_localizable, channel_locale.locale_code, '<all_locales>')
                ) AS elasticsearch_field_name
            FROM channel_locale
            JOIN pim_catalog_attribute attribute
            LEFT JOIN pim_catalog_attribute_locale pcal ON attribute.id = pcal.attribute_id AND pcal.locale_id = channel_locale.locale_id
            WHERE attribute_type = :attribute_type
        SQL;

        return $this->connection->executeQuery($sql, ['attribute_type' => $attributeType])->fetchAll(FetchMode::COLUMN);
    }

    private function countMediasFromES(array $fieldPaths): int
    {
        if (empty($fieldPaths)) {
            return 0;
        }

        $queries = array_map(function (string $fieldPath) {
            return  [
                [], //empty array needed before each query for multisearch in ES
                [
                    'size' => 0,
                    'query' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'term' => [
                                        'document_type' => ProductInterface::class,
                                    ],
                                ],
                                [
                                    'exists' => ['field' => $fieldPath],
                                ]
                            ]
                        ],
                    ],
                    'track_total_hits' => true
                ]
            ];
        }, $fieldPaths);

        $body = array_reduce($queries, 'array_merge', []);
        $rows = $this->client->msearch($body);

        $total = 0;
        foreach ($rows['responses'] as $row) {
            $total += $row['hits']['total']['value'] ?? 0;
        }

        return $total;
    }
}
