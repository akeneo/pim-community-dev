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

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\AssetFamily\Hydrator;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

/**
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ConnectorAssetFamilyHydrator
{
    public function __construct(
        private Connection $connection,
        private ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        private ConnectorTransformationCollectionHydrator $transformationCollectionHydrator,
        private ConnectorNamingConventionHydrator $namingConventionHydrator,
        private FindActivatedLocalesInterface $findActivatedLocales
    ) {
    }

    public function hydrate(array $row): ConnectorAssetFamily
    {
        $platform = $this->connection->getDatabasePlatform();
        $labels = Type::getType(Types::JSON)
            ->convertToPHPValue($row['labels'], $platform);
        $identifier = Type::getType(Types::STRING)
            ->convertToPHPValue($row['identifier'], $platform);
        $imageKey = Type::getType(Types::STRING)
            ->convertToPHPValue($row['image_file_key'], $platform);
        $imageFilename = Type::getType(Types::STRING)
            ->convertToPHPValue($row['image_original_filename'], $platform);
        $ruleTemplates = Type::getType(Types::JSON)
            ->convertToPHPValue($row['rule_templates'], $platform);
        $transformations = Type::getType(Types::JSON)
            ->convertToPHPValue($row['transformations'], $platform);
        $namingConvention = Type::getType(Types::JSON)
            ->convertToPHPValue($row['naming_convention'], $platform);
        $attributeAsMainMediaCode = Type::getType(Types::STRING)
            ->convertToPHPValue($row['attribute_as_main_media'], $platform);

        $image = Image::createEmpty();

        if (isset($row['image_file_key'])) {
            $file = new FileInfo();
            $file->setKey($imageKey);
            $file->setOriginalFilename($imageFilename);
            $image = Image::fromFileInfo($file);
        }

        $activatedLocales = $this->findActivatedLocales->findAll();
        $labels = $this->getLabelsByActivatedLocales($labels, $activatedLocales);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($identifier);
        $productLinkRules = $this->productLinkRulesHydrator->hydrate($ruleTemplates);
        $readTransformations = $this->transformationCollectionHydrator->hydrate(
            $transformations,
            $assetFamilyIdentifier
        );
        $readNamingConvention = $this->namingConventionHydrator->hydrate($namingConvention, $assetFamilyIdentifier);
        if (null !== $attributeAsMainMediaCode) {
            $attributeAsMainMediaCode = AttributeCode::fromString($attributeAsMainMediaCode);
        }

        return new ConnectorAssetFamily(
            $assetFamilyIdentifier,
            LabelCollection::fromArray($labels),
            $image,
            $productLinkRules,
            $readTransformations,
            $readNamingConvention,
            $attributeAsMainMediaCode
        );
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
