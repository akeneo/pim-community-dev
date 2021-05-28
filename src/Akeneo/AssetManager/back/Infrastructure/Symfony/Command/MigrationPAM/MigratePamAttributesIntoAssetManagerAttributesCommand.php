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

    private Connection $connection;

    private ?SymfonyStyle $io = null;

    /** @var string */
    private $assetFamilyCode;

    public function __construct(Connection $connection)
    {
        parent::__construct($this::$defaultName);

        $this->connection = $connection;
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

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $attributeCodes = $input->getArgument('attribute-codes');

        $this->io = new SymfonyStyle($input, $output);
        $this->assetFamilyCode = $input->getArgument('asset-family-code');

        $count = count($attributeCodes) === 0 ? $this->updateAll() : $this->updateFromAttributeCodes($attributeCodes);

        $this->io->success(sprintf('Success! %d former attribute(s) updated.', $count));
    }

    private function updateAll(): int
    {
        $sqlCount = <<<SQL
SELECT COUNT(1)
FROM pim_catalog_attribute 
WHERE attribute_type='pim_assets_collection'
SQL;
        $statement = $this->connection->executeQuery($sqlCount);
        $count = (int) $statement->fetchColumn();

        $sql = <<<SQL
UPDATE pim_catalog_attribute 
SET attribute_type='pim_catalog_asset_collection', properties=:properties
WHERE attribute_type='pim_assets_collection'
SQL;

        $this->connection->executeQuery($sql,
            ['properties' => serialize(['reference_data_name' => $this->assetFamilyCode])],
            ['properties' => \PDO::PARAM_STR]
        );

        return $count;
    }

    private function updateFromAttributeCodes(array $attributeCodes): int
    {
        $sqlCount = <<<SQL
SELECT COUNT(1)
FROM pim_catalog_attribute 
WHERE (attribute_type='pim_assets_collection' OR attribute_type='pim_catalog_asset_collection')
AND code IN (:attributeCodes)
SQL;
        $statement = $this->connection->executeQuery($sqlCount,
            ['attributeCodes' => $attributeCodes],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        );
        $count = (int) $statement->fetchColumn();

        $sql = <<<SQL
UPDATE pim_catalog_attribute 
SET attribute_type='pim_catalog_asset_collection', properties=:properties
WHERE (attribute_type='pim_assets_collection' OR attribute_type='pim_catalog_asset_collection')
AND code IN (:attributeCodes)
SQL;

        $this->connection->executeUpdate($sql,
            [
                'attributeCodes' => $attributeCodes,
                'properties' => serialize(['reference_data_name' => $this->assetFamilyCode])
            ],
            ['attributeCodes' => Connection::PARAM_STR_ARRAY]
        );

        return $count;
    }
}
