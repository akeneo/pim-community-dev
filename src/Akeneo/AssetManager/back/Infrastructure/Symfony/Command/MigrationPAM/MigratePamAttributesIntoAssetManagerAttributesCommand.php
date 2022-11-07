<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Symfony\Command\MigrationPAM;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigratePamAttributesIntoAssetManagerAttributesCommand extends Command
{
    protected static $defaultName = 'pimee:assets:migrate:migrate-pam-attributes';

    public function __construct(private Connection $connection)
    {
        parent::__construct($this::$defaultName);
    }

    protected function configure()
    {
        $this
            ->setHidden(true)
            ->setDescription('Update the former PIM attributes from PAM to Asset Manager')
            ->addArgument('asset-family-code', InputArgument::REQUIRED, 'The asset family code to link to')
            ->addArgument(
                'attribute-codes',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'List of attribute codes to update the attributes, separated by spaces. Keep it empty to update all attributes.'
            )
        ;
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $attributeCodes = $input->getArgument('attribute-codes');

        $io = new SymfonyStyle($input, $output);
        $assetFamilyCode = $input->getArgument('asset-family-code');

        $count = (is_countable($attributeCodes) ? count($attributeCodes) : 0) === 0 ? $this->updateAll($assetFamilyCode) : $this->updateFromAttributeCodes($assetFamilyCode, $attributeCodes);

        $io->success(sprintf('Success! %d former attribute(s) updated.', $count));

        return 0;
    }

    private function updateAll(string $assetFamilyCode): int
    {
        $sqlCount = <<<SQL
SELECT COUNT(1)
FROM pim_catalog_attribute 
WHERE attribute_type='pim_assets_collection'
SQL;
        $statement = $this->connection->executeQuery($sqlCount);
        $count = (int) $statement->fetchOne();

        $sql = <<<SQL
UPDATE pim_catalog_attribute 
SET attribute_type='pim_catalog_asset_collection', properties=:properties
WHERE attribute_type='pim_assets_collection'
SQL;

        $this->connection->executeQuery(
            $sql,
            ['properties' => serialize(['reference_data_name' => $assetFamilyCode])],
            ['properties' => \PDO::PARAM_STR]
        );

        return $count;
    }

    private function updateFromAttributeCodes(string $assetFamilyCode, array $attributeCodes): int
    {
        $sqlCount = <<<SQL
SELECT COUNT(1)
FROM pim_catalog_attribute 
WHERE (attribute_type='pim_assets_collection' OR attribute_type='pim_catalog_asset_collection')
AND code IN (:attributeCodes)
SQL;
        $statement = $this->connection->executeQuery(
            $sqlCount,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        );
        $count = (int) $statement->fetchOne();

        $sql = <<<SQL
UPDATE pim_catalog_attribute 
SET attribute_type='pim_catalog_asset_collection', properties=:properties
WHERE (attribute_type='pim_assets_collection' OR attribute_type='pim_catalog_asset_collection')
AND code IN (:attributeCodes)
SQL;

        $this->connection->executeStatement(
            $sql,
            [
                'attributeCodes' => $attributeCodes,
                'properties' => serialize(['reference_data_name' => $assetFamilyCode])
            ],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        );

        return $count;
    }
}
