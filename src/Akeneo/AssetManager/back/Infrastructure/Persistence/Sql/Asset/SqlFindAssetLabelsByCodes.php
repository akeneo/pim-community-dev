<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Asset\FindAssetLabelsByCodesInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\SqlFindAssetFamilyAttributeAsLabel;
use Doctrine\DBAL\Connection;
use PDO;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class SqlFindAssetLabelsByCodes implements FindAssetLabelsByCodesInterface
{
    private Connection $sqlConnection;

    private SqlFindAssetFamilyAttributeAsLabel $findAssetFamilyAttributeAsLabel;

    public function __construct(
        Connection $sqlConnection,
        SqlFindAssetFamilyAttributeAsLabel $findAssetFamilyAttributeAsLabel
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->findAssetFamilyAttributeAsLabel = $findAssetFamilyAttributeAsLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function find(AssetFamilyIdentifier $assetFamilyIdentifier, array $assetCodes): array
    {
        $fetch = <<<SQL
        SELECT code, value_collection
        FROM akeneo_asset_manager_asset
        WHERE code IN (:assetCodes) AND asset_family_identifier = :asset_family_identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            [
                'assetCodes' => $assetCodes,
                'asset_family_identifier' => (string) $assetFamilyIdentifier,
            ],
            [
                'assetCodes' => Connection::PARAM_STR_ARRAY
            ]
        );

        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $this->extractLabelsFromResults($results, $assetFamilyIdentifier);
    }

    private function extractLabelsFromResults(
        array $results,
        AssetFamilyIdentifier $assetFamilyIdentifier
    ): array {
        if (empty($results)) {
            return [];
        }

        $attributeAsLabelReference = $this->findAssetFamilyAttributeAsLabel->find($assetFamilyIdentifier);
        if ($attributeAsLabelReference->isEmpty()) {
            throw new \Exception(
                sprintf('No attribute as label has been defined for asset family "%s"', $assetFamilyIdentifier)
            );
        }

        $attributeAsLabel = $attributeAsLabelReference->normalize();

        $labelCollectionPerAsset = [];
        foreach ($results as $result) {
            $values = json_decode($result['value_collection'], true);
            $assetCode = $result['code'];

            $labelsIndexedPerLocale = [];
            foreach ($values as $value) {
                if ($value['attribute'] === $attributeAsLabel) {
                    $labelsIndexedPerLocale[$value['locale']] = $value['data'];
                }
            }

            $labelCollectionPerAsset[$assetCode] = LabelCollection::fromArray($labelsIndexedPerLocale);
        }

        return $labelCollectionPerAsset;
    }
}
