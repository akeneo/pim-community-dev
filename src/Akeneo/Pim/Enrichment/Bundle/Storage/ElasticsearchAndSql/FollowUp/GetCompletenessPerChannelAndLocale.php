<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\FollowUp;

use Akeneo\Pim\Enrichment\Component\FollowUp\Query\GetCompletenessPerChannelAndLocaleInterface;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCompletenessPerChannelAndLocale implements GetCompletenessPerChannelAndLocaleInterface
{
    /** @var Connection */
    private $connection;

    /** @var Client */
    private $client;

    /**
     * @param Connection $connection
     * @param Client     $client
     */
    public function __construct(Connection $connection, Client $client)
    {
        $this->connection = $connection;
        $this->client = $client;
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $translationLocaleCode): CompletenessWidget
    {
        $categoriesCodeAndLocalesByChannel = $this->getCategoriesCodesAndLocalesByChannel();

        $totalProductsByChannel = $this->countTotalProductsInCategoriesByChannel($categoriesCodeAndLocalesByChannel);
        $localesWithNbCompleteByChannel = $this->countTotalProductInCategoriesByChannelAndLocale($categoriesCodeAndLocalesByChannel);

        return $this->generateCompletenessWidgetModel(
            $translationLocaleCode,
            $categoriesCodeAndLocalesByChannel,
            $totalProductsByChannel,
            $localesWithNbCompleteByChannel
        );
    }

    /**
     * Search, by channel, all categories children code and active locales
     *
     * @return array
     *
     *          [channel_code, channel_labels, [categoryCodes], [locales]]
     *
     *      ex : ['ecommerce', ['en_US' => 'Ecommerce'...], ['print','cameras'...], ['de_DE','fr_FR'...]]
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getCategoriesCodesAndLocalesByChannel(): array
    {
        $sql = <<<SQL
            SELECT
                channel.code as channel_code,
                channel_translation.labels as channel_labels,
                JSON_ARRAY_APPEND(COALESCE(child.children_codes, "[]"), '$', root.code) as category_codes_in_channel,
                pim_locales.json_locales as locales
            FROM
                pim_catalog_category AS root
                LEFT JOIN
                (
                    SELECT
                        child.root as root_id,
                        JSON_ARRAYAGG(child.code) as children_codes
                    FROM
                        pim_catalog_category child
                    WHERE
                        child.parent_id IS NOT NULL
                    GROUP BY
                        child.root
                ) AS child ON root.id = child.root_id
                JOIN pim_catalog_channel as channel ON root.id = channel.category_id
                LEFT JOIN
                (
                    SELECT
                        channel.channel_code,
                        JSON_OBJECTAGG(locale.code, channel_translation.label) as labels
                    FROM
                        (SELECT DISTINCT code as channel_code, id as channel_id FROM pim_catalog_channel) AS channel
                    CROSS JOIN pim_catalog_locale locale
                    LEFT JOIN pim_catalog_channel_translation channel_translation ON channel_translation.foreign_key = channel.channel_id AND channel_translation.locale = locale.code
                    WHERE locale.is_activated = true
                    GROUP BY channel.channel_code
                ) AS channel_translation on channel_translation.channel_code = channel.code
                LEFT JOIN
                (
                    SELECT
                      channel.code as channel_code,
                      channel.category_id,
                      JSON_ARRAYAGG(locale.code) as json_locales
                    FROM pim_catalog_channel as channel
                    LEFT JOIN pim_catalog_channel_locale channel_locale ON channel.id = channel_locale.channel_id
                    LEFT JOIN pim_catalog_locale locale ON channel_locale.locale_id = locale.id
                    GROUP BY
                        channel.code
                ) AS pim_locales on pim_locales.channel_code = channel.code
            WHERE
                root.parent_id IS NULL 
            ORDER BY
                channel.code, root.code
SQL;

        $rows = $this->connection->executeQuery($sql)->fetchAll();

        foreach ($rows as $i => $categoriesCodeAndLocalesByChannel) {
            $rows[$i]['locales'] = \json_decode($categoriesCodeAndLocalesByChannel['locales']);
            $rows[$i]['category_codes_in_channel'] = \json_decode($categoriesCodeAndLocalesByChannel['category_codes_in_channel']);
            $rows[$i]['channel_labels'] = \json_decode($categoriesCodeAndLocalesByChannel['channel_labels'], true);
        }

        return $rows;
    }

    /**
     * Count with Elasticsearch the total number of products in categories, by channel
     *
     * @param array $categoriesCodeAndLocalesByChannels
     *
     * @return array
     *      ex: ['ecommerce' => 1259 ]
     */
    private function countTotalProductsInCategoriesByChannel(array $categoriesCodeAndLocalesByChannels): array
    {
        if (empty($categoriesCodeAndLocalesByChannels)) {
            return null;
        }

        $body = [];

        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $body[] = []; // header
            $body[] = [
                'size' => 0,
                'track_total_hits' => true,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => [
                                            'categories' => $categoriesCodeAndLocalesByChannel['category_codes_in_channel']
                                        ],
                                    ],
                                    [
                                        'term' => ["enabled" => true]
                                    ],
                                    [
                                        'term' => ['document_type' => ProductInterface::class]
                                    ],
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        $rows = $this->client->msearch($body);

        $index = 0;
        $totalProductsByChannel = [];

        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $nbTotalProducts = $rows['responses'][$index]['hits']['total']['value'] ?? -1;
            $totalProductsByChannel[$categoriesCodeAndLocalesByChannel['channel_code']] = $nbTotalProducts;
            $index++;
        }

        return $totalProductsByChannel;
    }

    /**
     * Count with Elasticsearch the total number of products in categories, by channel and by locale
     *
     * @param array $categoriesCodeAndLocalesByChannels
     * @return array
     *      ex: ['ecommerce' => [
     *              'locales' => ['fr_Fr' => 15, 'de_DE' => 1, 'en_US' => 5],
     *              'total' => 21
     *      ] ]
     */
    private function countTotalProductInCategoriesByChannelAndLocale(array $categoriesCodeAndLocalesByChannels): array
    {
        if (empty($categoriesCodeAndLocalesByChannels)) {
            return null;
        }

        $body = [];
        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            foreach ($categoriesCodeAndLocalesByChannel['locales'] as $locale) {
                $body[] = []; // header
                $body[] = [
                    'size' => 0,
                    'track_total_hits' => true,
                    'query' => [
                        'constant_score' => [
                            'filter' => [
                                'bool' => [
                                    'filter' => [
                                        [
                                            'terms' => [
                                                'categories' => $categoriesCodeAndLocalesByChannel['category_codes_in_channel']
                                            ]
                                        ],
                                        [
                                            'term' => ["completeness." . $categoriesCodeAndLocalesByChannel['channel_code'] . "." . $locale => 100]],
                                        [
                                            'term' => ["enabled" => true]
                                        ],
                                        [
                                            'term' => ['document_type' => ProductInterface::class]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }

        $rows = $this->client->msearch($body);

        $index = 0;
        $localesWithNbCompleteByChannel = [];

        foreach ($categoriesCodeAndLocalesByChannels as $i => $categoriesCodeAndLocalesByChannel) {
            $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']] = [];
            $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']]['total'] = 0;
            $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']]['locales'] = [];
            foreach ($categoriesCodeAndLocalesByChannel['locales'] as $locale) {
                $total = $rows['responses'][$index]['hits']['total']['value'] ?? -1;
                $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']]['locales'][$locale] = $total;
                $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']]['total'] += $total;
                $index++;
            }
        }

        return $localesWithNbCompleteByChannel;
    }

    /**
     * Merge all the data in a CompletenessWidget model
     *
     * @param string $translationLocaleCode
     * @param array $categoriesCodeAndLocalesByChannels
     * @param array $totalProductsByChannel
     * @param array $localesWithNbCompleteByChannel
     * @return CompletenessWidget
     */
    private function generateCompletenessWidgetModel(
            string $translationLocaleCode,
            array $categoriesCodeAndLocalesByChannels,
            array $totalProductsByChannel,
            array $localesWithNbCompleteByChannel
    ) {
        $channelCompletenesses = [];
        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $channelCode = $categoriesCodeAndLocalesByChannel['channel_code'];
            $channelLabels = $categoriesCodeAndLocalesByChannel['channel_labels'];

            $localeCompletenesses = [];
            foreach ($categoriesCodeAndLocalesByChannel['locales'] as $localeCode) {
                $locale = \Locale::getDisplayName($localeCode, $translationLocaleCode);
                $localeCompleteness = new LocaleCompleteness($locale, $localesWithNbCompleteByChannel[$channelCode]['locales'][$localeCode]);

                if (!in_array($localeCompleteness, $localeCompletenesses, true)) {
                    $localeCompletenesses[$localeCompleteness->locale()] = $localeCompleteness;
                }
            }

            $channelCompleteness = new ChannelCompleteness(
                $channelCode,
                $localesWithNbCompleteByChannel[$channelCode]['total'],
                $totalProductsByChannel[$channelCode],
                $localeCompletenesses,
                $channelLabels
            );

            if (!in_array($channelCompleteness, $channelCompletenesses, true)) {
                $channelCompletenesses[$channelCompleteness->channel()] = $channelCompleteness;
            }
        }

        $completenessWidget = new CompletenessWidget($channelCompletenesses);
        return $completenessWidget;
    }
}
