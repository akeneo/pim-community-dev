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
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MigrateReferenceAttributeHavingOneValuePerChannel extends Command
{
    protected static $defaultName = 'pimee:assets:migrate:reference-attribute-having-one-value-per-channel';

    private const DEFAULT_REFERENCE_CODE = 'reference';
    private const DEFAULT_REFERENCE_LOCALIZABLE_CODE = 'reference_localizable';

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        parent::__construct($this::$defaultName);

        $this->connection = $connection;
    }

    protected function configure()
    {
        $this
            ->setHidden(true)
            ->setDescription('Switch the value per channel attribute of asset family to a simple attribute')
            ->addArgument('asset-family-code', InputArgument::REQUIRED, 'The asset family code to migrate (if you want to force it)')
            ->addArgument('reference-code', InputArgument::OPTIONAL, 'the reference attribute code', self::DEFAULT_REFERENCE_CODE)
            ->addArgument(
                'reference-localizable-code',
                InputArgument::OPTIONAL,
                'the localizable reference attribute code',
                self::DEFAULT_REFERENCE_LOCALIZABLE_CODE
            );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Convertion of reference attribute to non scopable reference attributes'));
        $familyCode = $input->getArgument('asset-family-code');
        $referenceCode = $input->getArgument('reference-code');
        $referenceLocalizableCode = $input->getArgument('reference-localizable-code');

        if ($this->isAssetFamilyImpacted($familyCode, $referenceCode, $referenceLocalizableCode)) {
            $this->fixAllAssetsInFamily($familyCode, $referenceCode, $referenceLocalizableCode);
            $this->convertReferenceAttributesToNonScopable($familyCode, $referenceCode, $referenceLocalizableCode);
            $this->reIndexAssets($familyCode, $output);
        } else {
            $io->error(sprintf('The family %s doesn\'t seems to be impacted by the problem', $familyCode));
        }

        $io->success(sprintf('The family %s has been converted to a non scopable reference', $familyCode));
    }

    private function fixAllAssetsInFamily(string $assetFamilyCode, string $referenceCode, string $referenceLocalizableCode): void
    {
        $referenceAttributeIdentifiers = $this->getReferenceAttributeIdentifiers(
            $assetFamilyCode,
            $referenceCode,
            $referenceLocalizableCode
        );

        $batchSize = 100;
        $updatedAssets = [];
        foreach ($this->getAllAssets($assetFamilyCode) as $asset) {
            $updatedAssets[] = $this->fixAssetValues($asset, $referenceAttributeIdentifiers);

            if (count($updatedAssets) === $batchSize) {
                $this->writeFixedAssetsInDB($updatedAssets);
                $updatedAssets = [];
            }
        }

        if (0 !== count($updatedAssets)) {
            $this->writeFixedAssetsInDB($updatedAssets);
        }
    }

    private function writeFixedAssetsInDB($assets): void
    {
        $this->connection->beginTransaction();

        try {
            foreach ($assets as $asset) {
                $sqlAssetUpdate = <<<SQL
            UPDATE akeneo_asset_manager_asset SET value_collection = :value_collection WHERE identifier = :asset_identifier;
        SQL;
                $this->connection->executeUpdate(
                    $sqlAssetUpdate,
                    [
                        'value_collection' => $asset['value_collection'],
                        'asset_identifier' => $asset['identifier']
                    ],
                    [
                        'value_collection' => \PDO::PARAM_STR,
                        'asset_identifier' => \PDO::PARAM_STR
                    ]
                );
            }

            $this->connection->commit();
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    private function fixAssetValues(array $asset, array $referenceAttributeIdentifiers): array
    {
        $values = json_decode($asset['value_collection'], true);
        foreach ($values as $value) {
            if (in_array($value['attribute'], $referenceAttributeIdentifiers) && null !== $value['channel']) {
                $newKey = null === $value['locale'] ?
                    sprintf('%s', $value['attribute']) :
                    sprintf('%s_%s', $value['attribute'], $value['locale']);

                $values[$newKey] = array_merge($value, ['channel' => null]);
            }
        }

        return array_merge($asset, ['value_collection' => json_encode($values)]);
    }

    private function getAllAssets($assetFamilyCode): \Generator
    {
        $stmt = $this->connection->executeQuery(<<<SQL
SELECT *
FROM akeneo_asset_manager_asset
WHERE asset_family_identifier = :asset_family_identifier
SQL,
            [
                'asset_family_identifier' => $assetFamilyCode
            ],
            [
                'asset_family_identifier' => \PDO::PARAM_STR,
            ]
        );

        while ($asset = $stmt->fetch()) {
            yield $asset;
        }
    }

    private function convertReferenceAttributesToNonScopable(
        string $assetFamilyCode,
        string $referenceCode,
        string $referenceLocalizableCode
    ): void {
        $sqlReferenceAttributeUpdate = <<<SQL
UPDATE akeneo_asset_manager_attribute
SET value_per_channel = 0
WHERE (code = :reference_code OR code = :reference_localizable_code)
    AND attribute_type = 'media_file'
    AND value_per_channel = 1
    AND asset_family_identifier = :asset_family_identifier
SQL;
        $this->connection->executeQuery(
            $sqlReferenceAttributeUpdate,
            [
                'reference_code' => $referenceCode,
                'reference_localizable_code' => $referenceLocalizableCode,
                'asset_family_identifier' => $assetFamilyCode
            ],
            [
                'asset_family_identifier' => \PDO::PARAM_STR,
                'reference_code' => \PDO::PARAM_STR,
                'reference_localizable_code' => \PDO::PARAM_STR
            ]
        );
    }

    /**
     * Launch the full index command for the given asset family
     */
    private function reIndexAssets($assetFamilyCode, $output): void
    {
        $indexCommand = $this->getApplication()->find('akeneo:asset-manager:index-assets');
        $indexCommand->run(new ArrayInput(['asset_family_codes' => [$assetFamilyCode]]), $output);
    }

    private function isAssetFamilyImpacted(
        string $assetFamilyCode,
        string $referenceCode,
        string $referenceLocalizableCode
    ): bool {
        return count($this->getReferenceAttributeIdentifiers($assetFamilyCode, $referenceCode, $referenceLocalizableCode)) >= 1;
    }

    /**
     * Retrieve the reference attribute identifiers (localizable and non localizable that seems to be impacted)
     *
     * - it's code is "reference" or "reference_localizable"
     * - it of media_file type
     * - it has a value_per_channel at true
     */
    private function getReferenceAttributeIdentifiers(
        string $assetFamilyCode,
        string $referenceCode,
        string $referenceLocalizableCode
    ): array {
        $sqlReferenceAttributes = <<<SQL
SELECT identifier
FROM akeneo_asset_manager_attribute
WHERE (code = :reference_code OR code = :reference_localizable_code)
    AND attribute_type = 'media_file'
    AND value_per_channel = 1
    AND asset_family_identifier = :asset_family_identifier
SQL;

        return array_map(function ($row) {
            return $row['identifier'];
        }, $this->connection->fetchAll(
            $sqlReferenceAttributes,
            [
                'reference_code' => $referenceCode,
                'reference_localizable_code' => $referenceLocalizableCode,
                'asset_family_identifier' => $assetFamilyCode
            ],
            [
                'asset_family_identifier' => \PDO::PARAM_STR,
                'reference_code' => \PDO::PARAM_STR,
                'reference_localizable_code' => \PDO::PARAM_STR
            ]
        ));
    }
}
