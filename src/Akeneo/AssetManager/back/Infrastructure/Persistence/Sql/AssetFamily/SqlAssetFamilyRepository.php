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

use Akeneo\AssetManager\Domain\Event\AssetFamilyCreatedEvent;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplate;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAssetFamilyRepository implements AssetFamilyRepositoryInterface
{
    /** @var Connection */
    private $sqlConnection;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * @param Connection $sqlConnection
     */
    public function __construct(
        Connection $sqlConnection,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->sqlConnection = $sqlConnection;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function create(AssetFamily $assetFamily): void
    {
        $serializedLabels = $this->getSerializedLabels($assetFamily);
        $insert = <<<SQL
        INSERT INTO akeneo_asset_manager_asset_family 
            (identifier, labels, attribute_as_label, attribute_as_image, rule_templates) 
        VALUES 
            (:identifier, :labels, :attributeAsLabel, :attributeAsImage, :ruleTemplates);
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $insert,
            [
                'identifier' => (string) $assetFamily->getIdentifier(),
                'labels' => $serializedLabels,
                'attributeAsLabel' => $assetFamily->getAttributeAsLabelReference()->normalize(),
                'attributeAsImage' => $assetFamily->getAttributeAsImageReference()->normalize(),
                'ruleTemplates' => json_encode($assetFamily->getRuleTemplateCollection()->normalize())
            ]
        );
        if ($affectedRows !== 1) {
            throw new \RuntimeException(
                sprintf('Expected to create one asset family, but %d were affected', $affectedRows)
            );
        }

        $this->eventDispatcher->dispatch(
            AssetFamilyCreatedEvent::class,
            new AssetFamilyCreatedEvent($assetFamily->getIdentifier())
        );
    }

    /**
     * @throws \RuntimeException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function update(AssetFamily $assetFamily): void
    {
        $serializedLabels = $this->getSerializedLabels($assetFamily);
        $update = <<<SQL
        UPDATE akeneo_asset_manager_asset_family
        SET 
            labels = :labels, 
            image = :image, 
            attribute_as_label = :attributeAsLabel, 
            attribute_as_image = :attributeAsImage,
            rule_templates = :ruleTemplates
        WHERE identifier = :identifier;
SQL;
        $affectedRows = $this->sqlConnection->executeUpdate(
            $update,
            [
                'identifier' => (string) $assetFamily->getIdentifier(),
                'labels' => $serializedLabels,
                'image' => $assetFamily->getImage()->isEmpty() ? null : $assetFamily->getImage()->getKey(),
                'attributeAsLabel' => $assetFamily->getAttributeAsLabelReference()->normalize(),
                'attributeAsImage' => $assetFamily->getAttributeAsImageReference()->normalize(),
                'ruleTemplates' => json_encode($assetFamily->getRuleTemplateCollection()->normalize())
            ]
        );

        if ($affectedRows > 1) {
            throw new \RuntimeException(
                sprintf('Expected to update one asset family, but %d rows were affected.', $affectedRows)
            );
        }
    }

    public function getByIdentifier(AssetFamilyIdentifier $identifier): AssetFamily
    {
        $fetch = <<<SQL
        SELECT af.identifier, af.labels, fi.image, af.attribute_as_label, af.attribute_as_image, af.rule_templates
        FROM akeneo_asset_manager_asset_family af
        LEFT JOIN (
          SELECT file_key, JSON_OBJECT("file_key", file_key, "original_filename", original_filename) as image
          FROM akeneo_file_storage_file_info
        ) AS fi ON fi.file_key = af.image
        WHERE identifier = :identifier;
SQL;
        $statement = $this->sqlConnection->executeQuery(
            $fetch,
            ['identifier' => (string) $identifier]
        );
        $result = $statement->fetch();
        $statement->closeCursor();

        if (!$result) {
            throw AssetFamilyNotFoundException::withIdentifier($identifier);
        }

        return $this->hydrateAssetFamily(
            $result['identifier'],
            $result['labels'],
            null !== $result['image'] ? json_decode($result['image'], true) : null,
            $result['attribute_as_label'],
            $result['attribute_as_image'],
            $result['rule_templates']
        );
    }

    public function all(): \Iterator
    {
        $selectAllQuery = <<<SQL
        SELECT identifier, labels, attribute_as_label, attribute_as_image, rule_templates
        FROM akeneo_asset_manager_asset_family;
SQL;
        $statement = $this->sqlConnection->executeQuery($selectAllQuery);
        $results = $statement->fetchAll();
        $statement->closeCursor();

        foreach ($results as $result) {
            yield $this->hydrateAssetFamily(
                $result['identifier'],
                $result['labels'],
                null,
                $result['attribute_as_label'],
                $result['attribute_as_image'],
                $result['rule_templates']
            );
        }
    }

    public function deleteByIdentifier(AssetFamilyIdentifier $identifier): void
    {
        $sql = <<<SQL
        DELETE FROM akeneo_asset_manager_asset_family
        WHERE identifier = :identifier;
SQL;

        $affectedRows = $this->sqlConnection->executeUpdate(
            $sql,
            [
                'identifier' => $identifier
            ]
        );

        if (1 !== $affectedRows) {
            throw AssetFamilyNotFoundException::withIdentifier($identifier);
        }
    }

    public function count(): int
    {
        $query = <<<SQL
        SELECT COUNT(*) as total
        FROM akeneo_asset_manager_asset_family
SQL;
        $statement = $this->sqlConnection->executeQuery($query);
        $result = $statement->fetch();

        return intval($result['total']);
    }

    private function hydrateAssetFamily(
        string $identifier,
        string $normalizedLabels,
        ?array $image,
        ?string $attributeAsLabel,
        ?string $attributeAsImage,
        string $normalizedRuleTemplates
    ): AssetFamily {
        $platform = $this->sqlConnection->getDatabasePlatform();

        $labels = json_decode($normalizedLabels, true);
        $identifier = Type::getType(Type::STRING)->convertToPhpValue($identifier, $platform);
        $entityImage = $this->hydrateImage($image);
        $ruleTemplateCollection = $this->hydrateRuleTemplates($normalizedRuleTemplates);

        $assetFamily = AssetFamily::createWithAttributes(
            AssetFamilyIdentifier::fromString($identifier),
            $labels,
            $entityImage,
            AttributeAsLabelReference::createFromNormalized($attributeAsLabel),
            AttributeAsImageReference::createFromNormalized($attributeAsImage),
            $ruleTemplateCollection
        );

        return $assetFamily;
    }

    private function getSerializedLabels(AssetFamily $assetFamily): string
    {
        $labels = [];
        foreach ($assetFamily->getLabelCodes() as $localeCode) {
            $labels[$localeCode] = $assetFamily->getLabel($localeCode);
        }

        return json_encode($labels);
    }

    private function hydrateImage(?array $imageData): Image
    {
        $image = Image::createEmpty();

        if (null !== $imageData) {
            $file = new FileInfo();
            $file->setKey($imageData['file_key']);
            $file->setOriginalFilename($imageData['original_filename']);
            $image = Image::fromFileInfo($file);
        }

        return $image;
    }

    private function hydrateRuleTemplates(string $normalizedRuleTemplates): RuleTemplateCollection
    {
        $ruleTemplates = [];
        $normalizedRuleTemplates = json_decode($normalizedRuleTemplates, true);

        foreach ($normalizedRuleTemplates as $ruleTemplate) {
            $ruleTemplates[] = RuleTemplate::createFromNormalized($ruleTemplate);
        }

        return RuleTemplateCollection::createFromNormalized($ruleTemplates);
    }
}
