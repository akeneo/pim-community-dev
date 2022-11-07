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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NamingConvention;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\Asset\CountAssetsInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyDetailsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorProductLinkRulesHydrator;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator\ConnectorTransformationCollectionHydrator;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAssetFamilyDetails implements FindAssetFamilyDetailsInterface
{
    public function __construct(
        private Connection $sqlConnection,
        private FindAttributesDetailsInterface $findAttributesDetails,
        private FindActivatedLocalesInterface $findActivatedLocales,
        private ConnectorTransformationCollectionHydrator $transformationCollectionHydrator,
        private ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        private CountAssetsInterface $assetsCount,
    ) {
    }

    /**
     * {@inheritdoc}
     *
     * @throws DBALException
     */
    public function find(AssetFamilyIdentifier $identifier): ?AssetFamilyDetails
    {
        $result = $this->fetchResult($identifier);

        if (empty($result)) {
            return null;
        }

        $attributesDetails = $this->findAttributesDetails->find($identifier);
        $assetCount = $this->assetsCount->forAssetFamily($identifier);

        return $this->hydrateAssetFamilyDetails(
            $result['identifier'],
            $result['labels'],
            $assetCount,
            $result['file_key'],
            $result['original_filename'],
            $attributesDetails,
            $result['attribute_as_label'],
            $result['attribute_as_main_media'],
            json_decode($result['transformations'], true),
            json_decode($result['naming_convention'], true),
            json_decode($result['rule_templates'], true)
        );
    }

    private function fetchResult(AssetFamilyIdentifier $identifier): array
    {
        $query = <<<SQL
        SELECT
            am.identifier,
            am.labels,
            am.attribute_as_label,
            am.attribute_as_main_media,
            am.transformations,
            COALESCE(am.naming_convention, '{}') as naming_convention,
            am.rule_templates,
            fi.file_key,
            fi.original_filename
        FROM akeneo_asset_manager_asset_family as am
        LEFT JOIN akeneo_file_storage_file_info AS fi ON fi.file_key = am.image
        WHERE am.identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $identifier,
        ]);

        $result = $statement->fetchAssociative();
        $statement->free();

        return $result ?: [];
    }

    /**
     * @throws DBALException
     */
    private function hydrateAssetFamilyDetails(
        string $identifier,
        string $normalizedLabels,
        int $assetCount,
        ?string $fileKey,
        ?string $originalFilename,
        array $attributesDetails,
        ?string $attributeAsLabel,
        ?string $attributeAsMainMedia,
        array $transformations,
        array $namingConvention,
        array $productLinkRules
    ): AssetFamilyDetails {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $activatedLocales = $this->findActivatedLocales->findAll();

        $labels = Type::getType(Types::JSON)->convertToPHPValue($normalizedLabels, $platform);
        $identifier = Type::getType(Types::STRING)->convertToPHPValue($identifier, $platform);

        $entityImage = Image::createEmpty();
        if (null !== $fileKey && null !== $originalFilename) {
            $file = new FileInfo();
            $file->setKey($fileKey);
            $file->setOriginalFilename($originalFilename);
            $entityImage = Image::fromFileInfo($file);
        }

        $labelsByActivatedLocales = $this->getLabelsByActivatedLocales($labels, $activatedLocales);

        $assetFamilyItem = new AssetFamilyDetails();
        $assetFamilyItem->identifier = AssetFamilyIdentifier::fromString($identifier);
        $assetFamilyItem->labels = LabelCollection::fromArray($labelsByActivatedLocales);
        $assetFamilyItem->image = $entityImage;
        $assetFamilyItem->assetCount = $assetCount;
        $assetFamilyItem->attributes = $attributesDetails;
        $assetFamilyItem->attributeAsLabel = AttributeAsLabelReference::createFromNormalized($attributeAsLabel);
        $assetFamilyItem->attributeAsMainMedia = AttributeAsMainMediaReference::createFromNormalized($attributeAsMainMedia);
        $assetFamilyItem->transformations = $this->transformationCollectionHydrator->hydrate(
            $transformations,
            AssetFamilyIdentifier::fromString($identifier)
        );
        $assetFamilyItem->namingConvention = NamingConvention::createFromNormalized($namingConvention);
        $assetFamilyItem->productLinkRules = $this->productLinkRulesHydrator->hydrate($productLinkRules);

        return $assetFamilyItem;
    }

    private function getLabelsByActivatedLocales(array $labels, array $activatedLocales): array
    {
        $filteredLabels = [];
        foreach ($labels as $localeCode => $label) {
            if (in_array($localeCode, $activatedLocales)) {
                $filteredLabels[$localeCode] = $label;
            }
        }

        return $filteredLabels;
    }
}
