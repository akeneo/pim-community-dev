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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\FindAssetLabelsByIdentifiersInterface;
use Akeneo\Channel\API\Query\FindLocales;
use Akeneo\Channel\API\Query\Locale;
use Doctrine\DBAL\Connection;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindAssetLabelsByIdentifiers implements FindAssetLabelsByIdentifiersInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private FindLocales $findLocales
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function find(array $assetIdentifiers): array
    {
        $activatedLocaleCodes = array_map(static fn (Locale $locale) => $locale->getCode(), $this->findLocales->findAllActivated());

        $fetch = <<<SQL
SELECT
    a.identifier,
    a.code,
    a.value_collection,
    af.attribute_as_label
FROM 
    akeneo_asset_manager_asset a
    JOIN akeneo_asset_manager_asset_family af ON a.asset_family_identifier = af.identifier
WHERE 
    a.identifier IN (:assetIdentifiers)
GROUP BY 
    identifier;
SQL;

        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'assetIdentifiers' => $assetIdentifiers,
            ],
            [
                'assetIdentifiers' => Connection::PARAM_STR_ARRAY
            ]
        );

        $assets = $statement->fetchAllAssociative();

        $labelsIndexedByAsset = [];
        foreach ($assets as $asset) {
            $labelsIndexedByAsset[$asset['identifier']] = [
                'code' => $asset['code'],
                'labels' => $this->getLabelsIndexedByLocale($asset, $activatedLocaleCodes),
            ];
        }

        return $labelsIndexedByAsset;
    }

    private function getLabelsIndexedByLocale(array $asset, array $activatedLocaleCodes): array
    {
        $values = json_decode($asset['value_collection'], true);
        $labels = [];

        foreach ($activatedLocaleCodes as $activatedLocaleCode) {
            $key = sprintf('%s_%s', $asset['attribute_as_label'], $activatedLocaleCode);
            $labels[$activatedLocaleCode] = key_exists($key, $values)
                ? $values[$key]['data']
                : null;
        }

        return $labels;
    }
}
