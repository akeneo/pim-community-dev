<?php declare(strict_types=1);

namespace Pim\Upgrade\Schema;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Migrations\IrreversibleMigrationException;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Asset manager migration.
 */
final class Version_4_0_20191206083026_transform_image_attributes_to_media_file_attributes extends AbstractMigration implements ContainerAwareInterface
{
    private const OLD_ATTRIBUTE_TYPE = 'image';
    private const NEW_ATTRIBUTE_TYPE = 'media_file';
    private const NEW_PROPERTY = 'media_type';
    private const NEW_PROPERTY_VALUE = 'image';

    /** * @var ContainerInterface */
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function getDescription(): string
    {
        return 'Adapts ImageAttributes into the new MediaFileAttributes';
    }

    public function down(Schema $schema) : void
    {
        $this->throwIrreversibleMigrationException();
    }

    public function up(Schema $schema) : void
    {
        $imageAttributes = $this->fetchImageAttributes();
        $mediaFileAttributes = $this->transformImageAttributesIntoMediaFileAttributes($imageAttributes);
        $this->updateAttributes($mediaFileAttributes);
    }

    private function fetchImageAttributes(): array
    {
        /** @var Connection $connection */
        $connection = $this->container->get('database_connection');
        $stmt = $connection->executeQuery(<<<SQL
SELECT identifier, attribute_type, additional_properties
FROM akeneo_asset_manager_attribute
WHERE attribute_type = :attribute_type
SQL,
            ['attribute_type' => self::OLD_ATTRIBUTE_TYPE]
        );
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $result = $this->denormalizeAdditionalProperties($result);

        return $result;
    }

    private function transformImageAttributesIntoMediaFileAttributes(array $imageAttributes): array
    {
        return array_map(function ($attribute) {
            $attribute['attribute_type'] = self::NEW_ATTRIBUTE_TYPE;
            $attribute['additional_properties'][self::NEW_PROPERTY] = self::NEW_PROPERTY_VALUE;

            return $attribute;
        }, $imageAttributes);
    }

    private function updateAttributes(array $mediaFileAttributes): void
    {
        $updateStatement = <<<SQL
UPDATE akeneo_asset_manager_attribute 
SET attribute_type = :attribute_type, additional_properties = :additional_properties
WHERE identifier = :attribute_identifier
SQL;

        array_walk($mediaFileAttributes, function ($mediaFileAttribute) use ($updateStatement) {
            $this->addSql(
                $updateStatement,
                [
                    'attribute_identifier' => $mediaFileAttribute['identifier'],
                    'attribute_type' => $mediaFileAttribute['attribute_type'],
                    'additional_properties' => $mediaFileAttribute['additional_properties']
                ],
                ['additional_properties' => Types::JSON]
            );
        });
    }

    private function denormalizeAdditionalProperties($result): array
    {
        return array_map(
            function ($attribute) {
                $attribute['additional_properties'] = json_decode($attribute['additional_properties'], true);

                return $attribute;
            },
            $result
        );
    }
}
