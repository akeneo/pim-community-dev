<?php
/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Bundle\Command;

use Doctrine\DBAL\Connection as DbalConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Fixes asset family identifiers stored with a different case in attributes properties
 *
 * @author  JM Leroux <jean-marie.leroux@akeneo.com>
 * @see     PIM-9753
 */
class CleanAssetFamilyIdentifiersInAssetCollectionAttributes extends Command
{
    protected static $defaultName = 'pim:asset-manager:clean-asset-family-in-asset-collection-attributes';

    private $dbalConnection;

    public function __construct(DbalConnection $dbalConnection)
    {
        parent::__construct();
        $this->dbalConnection = $dbalConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setDescription('Clean asset family identifiers in asset collection attributes (PIM-9753)')
            ->setHidden(true);
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->getAssetCollectionAttributes() as $attribute) {
            $attributeProperties = unserialize($attribute['properties']);
            if (!isset($attributeProperties['reference_data_name'])) {
                continue;
            }

            $assetFamilyIdentifier = $this->findExactAssetFamilyIdentifier($attributeProperties['reference_data_name']);
            if (null === $assetFamilyIdentifier
                || $assetFamilyIdentifier === $attributeProperties['reference_data_name']) {
                continue;
            }

            $attributeProperties['reference_data_name'] = $assetFamilyIdentifier;
            $attribute['properties'] = serialize($attributeProperties);

            $this->saveCleanedAttribute($attribute);
        }

        return 0;
    }

    private function getAssetCollectionAttributes(): \Generator
    {
        $query = <<<SQL
            SELECT id, properties FROM pim_catalog_attribute 
            WHERE attribute_type = 'pim_catalog_asset_collection';
SQL;

        $stmt = $this->dbalConnection->executeQuery($query);

        while ($attribute = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            yield $attribute;
        }
    }

    private function findExactAssetFamilyIdentifier(string $rawIdentifier): ?string
    {
        $query = 'SELECT identifier FROM akeneo_asset_manager_asset_family WHERE identifier = :identifier;';

        $exactAssetFamilyIdentifier = $this->dbalConnection->executeQuery(
            $query,
            ['identifier' => $rawIdentifier]
        )->fetchColumn();

        return is_string($exactAssetFamilyIdentifier) ? $exactAssetFamilyIdentifier : null;
    }

    private function saveCleanedAttribute(array $attribute): void
    {
        $query = 'UPDATE pim_catalog_attribute SET properties = :properties WHERE id = :id;';

        $this->dbalConnection->executeQuery($query, [
            'id' => $attribute['id'],
            'properties' => $attribute['properties'],
        ]);
    }
}
