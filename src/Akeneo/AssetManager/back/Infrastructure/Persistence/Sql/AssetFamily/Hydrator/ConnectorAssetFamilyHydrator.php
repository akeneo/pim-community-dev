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

    public function __construct(
        Connection $connection,
        ConnectorProductLinkRulesHydrator $productLinkRulesHydrator,
        ConnectorTransformationCollectionHydrator $transformationCollectionHydrator
    ) {
        $this->platform = $connection->getDatabasePlatform();
        $this->productLinkRulesHydrator = $productLinkRulesHydrator;
        $this->transformationCollectionHydrator = $transformationCollectionHydrator;
    }

    public function hydrate(array $row): ConnectorAssetFamily
    {
        $labels = Type::getType(Type::JSON_ARRAY)
            ->convertToPHPValue($row['labels'], $this->platform);
        $identifier = Type::getType(Type::STRING)
            ->convertToPHPValue($row['identifier'], $this->platform);
        $imageKey = Type::getType(Type::STRING)
            ->convertToPHPValue($row['image_file_key'], $this->platform);
        $imageFilename = Type::getType(Type::STRING)
            ->convertToPHPValue($row['image_original_filename'], $this->platform);
        $ruleTemplates = Type::getType(Type::JSON_ARRAY)
            ->convertToPHPValue($row['rule_templates'], $this->platform);
        $transformations = Type::getType(Type::JSON_ARRAY)
            ->convertToPHPValue($row['transformations'], $this->platform);

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

        return new ConnectorAssetFamily(
            $assetFamilyIdentifier,
            LabelCollection::fromArray($labels),
            $image,
            $productLinkRules,
            $readTransformations
        );
    }
}
