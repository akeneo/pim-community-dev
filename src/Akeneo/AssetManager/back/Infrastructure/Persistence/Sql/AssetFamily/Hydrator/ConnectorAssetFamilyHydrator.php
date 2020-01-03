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
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
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
    /** @var AbstractPlatform */
    private $platform;

    /** @var ConnectorProductLinkRulesHydrator */
    private $productLinkRulesHydrator;

    /** @var ConnectorTransformationCollectionHydrator */
    private $transformationCollectionHydrator;

    /** @var ConnectorNamingConventionHydrator */
    private $namingConventionHydrator;

    public function __construct(
        Connection $connection,
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        ConnectorTransformationCollectionHydrator $transformationCollectionHydrator,
        ConnectorNamingConventionHydrator $namingConventionHydrator
    ) {
        $this->platform = $connection->getDatabasePlatform();
        $this->productLinkRulesHydrator = $productLinkRulesHydrator;
        $this->transformationCollectionHydrator = $transformationCollectionHydrator;
        $this->namingConventionHydrator = $namingConventionHydrator;
    }

    public function hydrate(array $row): ConnectorAssetFamily
    {
        $labels = Type::getType(Types::JSON)
            ->convertToPHPValue($row['labels'], $this->platform);
        $identifier = Type::getType(Types::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $imageKey = Type::getType(Types::STRING)
            ->convertToPHPValue($row['image_file_key'], $this->platform);
        $imageFilename = Type::getType(Types::STRING)
            ->convertToPHPValue($row['image_original_filename'], $this->platform);
        $ruleTemplates = Type::getType(Types::JSON)
            ->convertToPHPValue($row['rule_templates'], $this->platform);
        $transformations = Type::getType(Types::JSON)
            ->convertToPHPValue($row['transformations'], $this->platform);
        $namingConvention = Type::getType(Types::JSON)
            ->convertToPHPValue($row['naming_convention'], $this->platform);

        $image = Image::createEmpty();

        if (isset($row['image_file_key'])) {
            $file = new FileInfo();
            $file->setKey($imageKey);
            $file->setOriginalFilename($imageFilename);
            $image = Image::fromFileInfo($file);
        }

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($identifier);
        $productLinkRules = $this->productLinkRulesHydrator->hydrate($ruleTemplates);
        $readTransformations = $this->transformationCollectionHydrator->hydrate(
            $transformations,
            $assetFamilyIdentifier
        );
        $readNamingConvention = $this->namingConventionHydrator->hydrate($namingConvention, $assetFamilyIdentifier);

        return new ConnectorAssetFamily(
            $assetFamilyIdentifier,
            LabelCollection::fromArray($labels),
            $image,
            $productLinkRules,
            $readTransformations,
            $readNamingConvention
        );
    }
}
