<?php

declare(strict_types=1);

namespace Pim\Bundle\EnrichBundle\Storage\ElasticsearchAndSql\FollowUp;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Doctrine\DBAL\Connection;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Enrich\FollowUp\Query\GetCompletenessPerChannelAndLocaleInterface;
use Pim\Component\Enrich\FollowUp\ReadModel\ChannelCompleteness;
use Pim\Component\Enrich\FollowUp\ReadModel\CompletenessWidget;
use Pim\Component\Enrich\FollowUp\ReadModel\LocaleCompleteness;

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
                IF (
                  COALESCE(child.children_codes, '') = '',
                  root.code,
                  CONCAT(COALESCE(child.children_codes, ''), ',', root.code)
                ) as category_codes_in_channel,
                COALESCE(pim_locales.json_locales, '') as locales
            FROM
                pim_catalog_category AS root
                LEFT JOIN
                (
                    SELECT
                        child.root as root_id,
                        GROUP_CONCAT(child.code) as children_codes
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
                      GROUP_CONCAT(locale.code) as json_locales
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

        return array_map(
            function (array $row) {
                $row['locales'] = \explode(',', $row['locales']);
                $row['category_codes_in_channel'] = \explode(',', $row['category_codes_in_channel']);

                return $row;
            },
            $this->connection->executeQuery($sql, ['locale' => $translationLocaleCode])->fetchAll()
        );
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

        $totalProductsByChannel = [];
        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $body = [
                'size' => 0,
                'query' => [
                    'constant_score' => [
                        'filter' => [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => [
                                            'categories' => $categoriesCodeAndLocalesByChannel['category_codes_in_channel'],
                                        ],
                                    ],
                                    [
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'term' => ["enabled" => true],
                                                ],
                                                [
                                                    'term' => [
                                                        'document_type' => ProductInterface::class,
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ];

            $result = $this->client->search($this->indexType, $body);

            $total = $result['hits']['total'] ?? -1;
            $totalProductsByChannel[$categoriesCodeAndLocalesByChannel['channel_code']] = $total;
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

        $localesWithNbCompleteByChannel = [];
        foreach ($categoriesCodeAndLocalesByChannels as $categoriesCodeAndLocalesByChannel) {
            $channelCode = $categoriesCodeAndLocalesByChannel['channel_code'];
            $localesWithNbCompleteByChannel[$channelCode] = [];
            $localesWithNbCompleteByChannel[$channelCode]['total'] = 0;
            $localesWithNbCompleteByChannel[$channelCode]['locales'] = [];

            foreach ($categoriesCodeAndLocalesByChannel['locales'] as $locale) {
                $body = [
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
                                                    ['term' => [sprintf('completeness.%s.%s', $channelCode, $locale) => 100]],
                                                ],
                                                'must' => [
                                                    [
                                                        'term' => ["enabled" => true]
                                                    ],
                                                    [
                                                        'term' => ['document_type' => ProductInterface::class],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ];

                $result = $this->client->search($this->indexType, $body);

                $total = $result['hits']['total'] ?? -1;
                $localesWithNbCompleteByChannel[$channelCode]['locales'][$locale] = $total;
                $localesWithNbCompleteByChannel[$channelCode]['total'] += $total;
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
