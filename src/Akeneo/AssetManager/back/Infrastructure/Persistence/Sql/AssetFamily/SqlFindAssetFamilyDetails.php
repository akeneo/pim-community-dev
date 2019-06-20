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
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyDetailsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\FindAttributesDetailsInterface;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindAssetFamilyDetails implements FindAssetFamilyDetailsInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var FindAttributesDetailsInterface */
    private $findAttributesDetails;

    /** @var FindActivatedLocalesInterface  */
    private $findActivatedLocales;

    public function __construct(
        Connection $sqlConnection,
        FindAttributesDetailsInterface $findAttributesDetails,
        FindActivatedLocalesInterface $findActivatedLocales
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->findAttributesDetails = $findAttributesDetails;
        $this->findActivatedLocales = $findActivatedLocales;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function find(AssetFamilyIdentifier $identifier): ?AssetFamilyDetails
    {
        $result = $this->fetchResult($identifier);

        if (empty($result)) {
            return null;
        }

        $attributesDetails = $this->findAttributesDetails->find($identifier);

        return $this->hydrateAssetFamilyDetails(
            $result['identifier'],
            $result['labels'],
            $result['asset_count'],
            $result['file_key'],
            $result['original_filename'],
            $attributesDetails,
            $result['attribute_as_label'],
            $result['attribute_as_image']
        );
    }

    private function fetchResult(AssetFamilyIdentifier $identifier): array
    {
        $query = <<<SQL
        SELECT
            re.identifier,
            re.labels,
            re.attribute_as_label,
            re.attribute_as_image,
            fi.file_key,
            fi.original_filename, (
                SELECT count(*) FROM akeneo_asset_manager_asset WHERE asset_family_identifier = :identifier
            ) as asset_count
        FROM akeneo_asset_manager_asset_family as re
        LEFT JOIN akeneo_file_storage_file_info AS fi ON fi.file_key = re.image
        WHERE re.identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery($query, [
            'identifier' => (string) $identifier,
        ]);

        $result = $statement->fetch(\PDO::FETCH_ASSOC);
        $statement->closeCursor();

        return !$result ? [] : $result;
    }

    /**
     * @return AssetFamilyDetails
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    private function hydrateAssetFamilyDetails(
        string $identifier,
        string $normalizedLabels,
        string $assetCount,
        ?string $fileKey,
        ?string $originalFilename,
        array $attributesDetails,
        ?string $attributeAsLabel,
        ?string $attributeAsImage
    ): AssetFamilyDetails {
        $platform = $this->sqlConnection->getDatabasePlatform();
        $activatedLocales = $this->findActivatedLocales->findAll();

        $labels = Type::getType(Type::JSON_ARRAY)->convertToPHPValue($normalizedLabels, $platform);
        $identifier = Type::getType(Type::STRING)->convertToPHPValue($identifier, $platform);
        $assetCount = Type::getType(Type::INTEGER)->convertToPHPValue($assetCount, $platform);

        $entityImage = Image::createEmpty();
        if (null !== $fileKey && null !== $originalFilename) {
            $file = new FileInfo();
            $file->setKey($fileKey);
            $file->setOriginalFilename($originalFilename);
            $entityImage=Image::fromFileInfo($file);
        }

        $labelsByActivatedLocales = $this->getLabelsByActivatedLocales($labels, $activatedLocales);

        $assetFamilyItem = new AssetFamilyDetails();
        $assetFamilyItem->identifier = AssetFamilyIdentifier::fromString($identifier);
        $assetFamilyItem->labels = LabelCollection::fromArray($labelsByActivatedLocales);
        $assetFamilyItem->image = $entityImage;
        $assetFamilyItem->assetCount = $assetCount;
        $assetFamilyItem->attributes = $attributesDetails;
        $assetFamilyItem->attributeAsLabel = AttributeAsLabelReference::createFromNormalized($attributeAsLabel);
        $assetFamilyItem->attributeAsImage = AttributeAsImageReference::createFromNormalized($attributeAsImage);

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
