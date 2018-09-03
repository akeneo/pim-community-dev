<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\FollowUp;

use Akeneo\Pim\Enrichment\Component\FollowUp\Query\GetCompletenessPerChannelAndLocaleInterface;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\ChannelCompleteness;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\CompletenessWidget;
use Akeneo\Pim\Enrichment\Component\FollowUp\ReadModel\LocaleCompleteness;
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

    /** @var string */
    private $indexType;

    /**
     * @param Connection $connection
     * @param Client     $client
     * @param string     $indexType
     */
    public function __construct(Connection $connection, Client $client, string $indexType)
    {
        $this->connection = $connection;
        $this->client = $client;
        $this->indexType = $indexType;
    }

    /**
     * @inheritdoc
     */
    public function fetch(string $translationLocaleCode): CompletenessWidget
    {
        $categoriesCodeAndLocalesByChannel = $this->getCategoriesCodesAndLocalesByChannel($translationLocaleCode);

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
     * @param string $translationLocaleCode
     * @return array
     *
     *          [channel_code, channel_label, [categoryCodes], [locales]]
     *
     *      ex : ['ecommerce', 'Ecommerce', ['print','cameras'...], ['de_DE','fr_FR'...]]
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function getCategoriesCodesAndLocalesByChannel(string $translationLocaleCode): array
    {
        $sql = <<<SQL
            SELECT
                channel.code as channel_code,
                COALESCE(channel_translation.label, CONCAT('[', channel.code, ']') ) as channel_label,
                JSON_ARRAY_APPEND(child.children_codes, '$', root.code) as category_codes_in_channel,
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
                LEFT JOIN pim_catalog_channel_translation as channel_translation ON channel.id = channel_translation.foreign_key AND channel_translation.locale = :locale
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

        $rows = $this->connection->executeQuery(
            $sql,
            [
                'locale' => $translationLocaleCode
            ]
        )->fetchAll();

        foreach ($rows as $i => $categoriesCodeAndLocalesByChannel) {
            $rows[$i]['locales'] = \json_decode($categoriesCodeAndLocalesByChannel['locales']);
            $rows[$i]['category_codes_in_channel'] = \json_decode($categoriesCodeAndLocalesByChannel['category_codes_in_channel']);
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
            $body[] = [];
            $body[] = [
                'size' => 0,
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
                                        'bool' => [
                                            'must' => [
                                                'term' => ["enabled" => true]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }

        $rows = $this->client->msearch($this->indexType, $body);

        $index = 0;
        $totalProductsByChannel = [];

        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $nbTotalProducts = $rows['responses'][$index]['hits']['total'] ?? -1;
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
                $body[] = [];
                $body[] = [
                    'size' => 0,
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
                                            'bool' => [
                                                'should' => [
                                                    ['term' => ["completeness." . $categoriesCodeAndLocalesByChannel['channel_code'] . "." . $locale => 100]]
                                                ],
                                                'must' => [
                                                    'term' => ["enabled" => true]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ];
            }
        }

        $rows = $this->client->msearch($this->indexType, $body);

        $index = 0;
        $localesWithNbCompleteByChannel = [];

        foreach ($categoriesCodeAndLocalesByChannels as $i => $categoriesCodeAndLocalesByChannel) {
            $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']] = [];
            $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']]['total'] = 0;
            $localesWithNbCompleteByChannel[$categoriesCodeAndLocalesByChannel['channel_code']]['locales'] = [];
            foreach ($categoriesCodeAndLocalesByChannel['locales'] as $locale) {
                $total = $rows['responses'][$index]['hits']['total'] ?? -1;
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
            $channelLabel = $categoriesCodeAndLocalesByChannel['channel_label'];

            $localeCompletenesses = [];
            foreach ($categoriesCodeAndLocalesByChannel['locales'] as $localeCode) {
                $locale = \Locale::getDisplayName($localeCode, $translationLocaleCode);
                $localeCompleteness = new LocaleCompleteness($locale, $localesWithNbCompleteByChannel[$channelCode]['locales'][$localeCode]);

                if (!in_array($localeCompleteness, $localeCompletenesses, true)) {
                    $localeCompletenesses[$localeCompleteness->locale()] = $localeCompleteness;
                }
            }

            $channelCompleteness = new ChannelCompleteness(
                $channelLabel,
                $localesWithNbCompleteByChannel[$channelCode]['total'],
                $totalProductsByChannel[$channelCode],
                $localeCompletenesses
            );

            if (!in_array($channelCompleteness, $channelCompletenesses, true)) {
                $channelCompletenesses[$channelCompleteness->channel()] = $channelCompleteness;
            }
        }

        $completenessWidget = new CompletenessWidget($channelCompletenesses);
        return $completenessWidget;
    }
}
