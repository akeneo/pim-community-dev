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

namespace AkeneoTestEnterprise\Pim\Enrichment\AssetManager\Integration\Export;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsMainMediaReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\MediaType as MediaLinkMediaType;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Prefix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLink\Suffix;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\Pim\Enrichment\AssetManager\Component\AttributeType\AssetCollectionType;
use Akeneo\Tool\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Tool\Component\Connector\Reader\File\Xlsx\Reader;
use Akeneo\Tool\Component\Connector\Writer\File\AbstractItemMediaWriter;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use AkeneoTest\Pim\Enrichment\Integration\Product\Export\AbstractExportTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractExportWithAssetTestCase extends AbstractExportTestCase
{
    protected const ASSET_FAMILY_WITH_FILE_AS_MAIN_MEDA = 'designer';
    protected const ASSET_FAMILY_WITH_LINK_AS_MAIN_MEDA = 'atmosphere';

    protected FileStorer $fileStorer;
    protected AssetFamilyRepositoryInterface $assetFamilyRepository;
    protected AttributeRepositoryInterface $attributeRepository;
    protected AssetRepositoryInterface $assetRepository;

    public function setUp(): void
    {
        parent::setUp();

        $this->fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $this->assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');

        $this->loadAssets($this->loadAssetFamilyWithMediaFileAsMainMedia());
        $this->loadAssets($this->loadAssetFamilyWithMediaLinkAsMainMedia());
    }

    abstract protected function getWriter(): AbstractItemMediaWriter;

    private function loadAssetFamilyWithMediaFileAsMainMedia(): AssetFamily
    {
        $identifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_WITH_FILE_AS_MAIN_MEDA);

        // A "media" attribute is automatically created
        $this->assetFamilyRepository->create(
            AssetFamily::create(
                $identifier,
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );

        return $this->assetFamilyRepository->getByIdentifier($identifier);
    }

    private function loadAssetFamilyWithMediaLinkAsMainMedia(): AssetFamily
    {
        $atmosphereAssetFamilyIdentifier = AssetFamilyIdentifier::fromString(self::ASSET_FAMILY_WITH_LINK_AS_MAIN_MEDA);
        $atmosphere = AssetFamily::create(
            $atmosphereAssetFamilyIdentifier,
            ['en_US' => 'Athmospheres'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($atmosphere);

        // Attributes
        $linkAtmosphere = MediaLinkAttribute::create(
            AttributeIdentifier::create(self::ASSET_FAMILY_WITH_LINK_AS_MAIN_MEDA, 'link_atmosphere', 'fingerprint'),
            $atmosphereAssetFamilyIdentifier,
            AttributeCode::fromString('link_atmosphere'),
            LabelCollection::fromArray(['en_US' => 'Link atmosphere']),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            Prefix::empty(),
            Suffix::empty(),
            MediaLinkMediaType::fromString(MediaLinkMediaType::IMAGE)
        );
        $this->attributeRepository->create($linkAtmosphere);

        $updatedAtmosphere = $this->assetFamilyRepository->getByIdentifier(
            $atmosphereAssetFamilyIdentifier
        );
        $updatedAtmosphere->updateAttributeAsMainMediaReference(
            AttributeAsMainMediaReference::fromAttributeIdentifier($linkAtmosphere->getIdentifier())
        );
        $this->assetFamilyRepository->update($updatedAtmosphere);

        return $this->assetFamilyRepository->getByIdentifier($atmosphereAssetFamilyIdentifier);
    }

    protected function loadAssets(AssetFamily $assetFamily): void
    {
        $assetFamily->getAttributeAsMainMediaReference()->getIdentifier();
        $attributeAsMainMedia = $this->attributeRepository->getByIdentifier(
            $assetFamily->getAttributeAsMainMediaReference()->getIdentifier()
        );

        if ($attributeAsMainMedia instanceof MediaFileAttribute) {
            $imageFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'file1.gif';
            self::assertTrue(touch($imageFilename));
            $file = $this->fileStorer->store(new \SplFileInfo($imageFilename), Storage::FILE_STORAGE_ALIAS);

            $imageData = FileData::createFromNormalize([
                'filePath'         => $file->getKey(),
                'originalFilename' => $file->getOriginalFilename(),
                'size'             => 1024,
                'mimeType'         => $file->getMimeType(),
                'extension'        => $file->getExtension(),
                'updatedAt'        => '2019-11-22T15:16:21+0000',
            ]);
        } else {
            $imageData = MediaLinkData::fromString('http://www.example.com/link1');
        }

        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString($assetFamily->getIdentifier() . '_asset1'),
                $assetFamily->getIdentifier(),
                AssetCode::fromString('asset1'),
                ValueCollection::fromValues([
                    Value::create(
                        $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        $imageData
                    ),
                ])
            )
        );

        if ($attributeAsMainMedia instanceof MediaFileAttribute) {
            $imageFilename = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'file2.gif';
            self::assertTrue(touch($imageFilename));
            $file = $this->fileStorer->store(new \SplFileInfo($imageFilename), Storage::FILE_STORAGE_ALIAS);

            $imageData = FileData::createFromNormalize([
                'filePath'         => $file->getKey(),
                'originalFilename' => $file->getOriginalFilename(),
                'size'             => 1024,
                'mimeType'         => $file->getMimeType(),
                'extension'        => $file->getExtension(),
                'updatedAt'        => '2019-11-22T15:16:21+0000',
            ]);
        } else {
            $imageData = MediaLinkData::fromString('http://www.example.com/link2');
        }

        $this->assetRepository->create(
            Asset::create(
                AssetIdentifier::fromString($assetFamily->getIdentifier() . '_asset2'),
                $assetFamily->getIdentifier(),
                AssetCode::fromString('asset2'),
                ValueCollection::fromValues([
                    Value::create(
                        $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        $imageData
                    ),
                ])
            )
        );

        $this->flushAssetsToIndexCache();
    }

    protected function flushAssetsToIndexCache(): void
    {
        // Flushes the assets to index cache in the subscriber
        $this->get('akeneo_assetmanager.infrastructure.search.elasticsearch.asset.index_asset_event_aggregator')->flushEvents();

        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();
    }

    protected function loadProductsWithAssetFamilyReferenceData(string $assetFamilyReferenceData): void
    {
        $this->createAttribute([
            'code' => 'asset_attribute',
            'type' => AssetCollectionType::ASSET_COLLECTION,
            'group' => 'attributeGroupA',
            'localizable' => false,
            'scopable' => false,
            'reference_data_name' => $assetFamilyReferenceData,
        ]);
        $this->createAttribute([
            'code' => 'localizable_asset_attribute',
            'type' => AssetCollectionType::ASSET_COLLECTION,
            'group' => 'attributeGroupA',
            'localizable' => true,
            'scopable' => false,
            'reference_data_name' => $assetFamilyReferenceData,
        ]);

        $this->createProduct(
            'product_1',
            [
                'parent' => null,
                'values' => [
                    'asset_attribute' => [['locale' => null, 'scope' => null, 'data' => ['asset1', 'asset2']]],
                ],
            ]
        );

        $this->createProduct(
            'product_2',
            [
                'parent' => null,
                'values' => [
                    'localizable_asset_attribute' => [
                        ['locale' => 'en_US', 'scope' => null, 'data' => ['asset1', 'asset2']],
                        ['locale' => 'fr_FR', 'scope' => null, 'data' => ['asset2']],
                        ['locale' => 'de_DE', 'scope' => null, 'data' => ['asset1']],
                    ],
                ],
            ]
        );
    }

    protected function loadProductModelsWithAssetFamilyReferenceData(string $assetFamilyReferenceData): void
    {
        $this->createAttribute([
            'code'        => 'color',
            'type'        => 'pim_catalog_simpleselect',
            'group'       => 'attributeGroupA',
            'localizable' => false,
            'scopable'    => false,
        ]);
        $this->createAttributeOption([
            'code'        => 'blue',
            'attribute'   => 'color',
        ]);
        $this->createAttributeOption([
            'code'        => 'pink',
            'attribute'   => 'color',
        ]);
        if (!$this->attributeExists('asset_attribute')) {
            $this->createAttribute([
                'code' => 'asset_attribute',
                'type' => AssetCollectionType::ASSET_COLLECTION,
                'group' => 'attributeGroupA',
                'localizable' => false,
                'scopable' => false,
                'reference_data_name' => $assetFamilyReferenceData,
            ]);
        }
        if (!$this->attributeExists('localizable_asset_attribute')) {
            $this->createAttribute([
                'code' => 'localizable_asset_attribute',
                'type' => AssetCollectionType::ASSET_COLLECTION,
                'group' => 'attributeGroupA',
                'localizable' => true,
                'scopable' => false,
                'reference_data_name' => $assetFamilyReferenceData,
            ]);
        }
        $this->createFamily([
            'code'        => 'clothing',
            'attributes'  => ['color', 'asset_attribute', 'localizable_asset_attribute'],
            'attribute_requirements' => [
                'tablet' => ['sku']
            ],
        ]);
        $this->createFamilyVariant([
            'code' => 'clothing_color',
            'family' => 'clothing',
            'variant_attribute_sets' => [
                [
                    'level' => 1,
                    'axes' => ['color'],
                    'attributes' => ['color'],
                ],
            ],
        ]);

        $this->createProductModel([
            'code' => 'product_model_1',
            'family_variant' => 'clothing_color',
            'parent' => null,
            'categories' => [],
            'values'  => [
                'asset_attribute' => [['locale' => null, 'scope' => null, 'data' => ['asset1', 'asset2']]],
            ]
        ]);
        $this->createProductModel([
            'code' => 'product_model_2',
            'family_variant' => 'clothing_color',
            'parent' => null,
            'categories' => [],
            'values'  => [
                'localizable_asset_attribute' => [
                    ['locale' => 'en_US', 'scope' => null, 'data' => ['asset1', 'asset2']],
                    ['locale' => 'fr_FR', 'scope' => null, 'data' => ['asset2']],
                    ['locale' => 'de_DE', 'scope' => null, 'data' => ['asset1']],
                ],
            ]
        ]);
    }

    private function attributeExists(string $attributeCode): bool
    {
        return null !== $this->get('pim_catalog.repository.attribute')->findOneByIdentifier($attributeCode);
    }

    protected function assertFilePaths(string $value, array $expectedFilePaths): void
    {
        $filePaths = explode(',', $value);
        self::assertSame(count($expectedFilePaths), count($filePaths));
        foreach ($expectedFilePaths as $expectedFilePath) {
            self::assertContains($expectedFilePath, $filePaths);
        }
    }

    protected function getWorkingPath(): string
    {
        return sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'export_with_assets';
    }

    protected function launchCsvExportAndReturnArrayResults(
        string $jobCode,
        string $username = null,
        array $config = []
    ): array {
        $filePath = $this->getWorkingPath() . DIRECTORY_SEPARATOR . 'export.csv';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $config['filePath'] = $filePath;
        $this->launchCsvExport($jobCode, $username, $config);

        return $this->getResultsFromExportedFile($filePath);
    }

    protected function launchCsvExport(string $jobCode, string $username = null, array $config = []): void
    {
        $application = new Application($this->get('kernel'));
        $application->setAutoExit(false);

        $arrayInput = [
            'command'  => 'akeneo:batch:job',
            'code'     => $jobCode,
            '--config' => json_encode($config),
            '--no-log' => true,
            '-v'       => true
        ];

        if (null !== $username) {
            $arrayInput['--username'] = $username;
        }

        $input = new ArrayInput($arrayInput);

        $output = new BufferedOutput();
        $exitCode = $application->run($input, $output);

        if (BatchCommand::EXIT_SUCCESS_CODE !== $exitCode) {
            throw new \Exception(sprintf('Export failed, "%s".', $output->fetch()));
        }
    }

    protected function getResultsFromExportedFile(string $exportedFilePath): array
    {
        if (!is_readable($exportedFilePath)) {
            throw new \Exception(sprintf('Exported "%s" file is not readable.', $exportedFilePath));
        }

        $csv = array_map(fn (string $line) => str_getcsv($line, ';'), file($exportedFilePath));
        foreach ($csv as $line => $csvLine) {
            if (0 === $line) {
                continue;
            }

            foreach ($csvLine as $key => $content) {
                $csv[$line][$csv[0][$key]] = $content;
                unset($csv[$line][$key]);
            }
        }
        array_shift($csv); # remove column header

        return $csv;
    }

    protected function assertFileExistsInWorkingPath(string $file): void
    {
        self::assertFileExists(
            $this->getWorkingPath() . '/' . $file,
            sprintf(
                "The '%s' file is not found. Got: \n%s",
                $this->getWorkingPath() . '/' . $file,
                $this->scanDirectoryRecursively($this->getWorkingPath())
            )
        );
        self::assertContains($file, $this->getWriter()->getWrittenFiles(), 'The file will not be present in archive');
    }

    protected function scanDirectoryRecursively(string $dir): string
    {
        $list = '';
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    $list .= "$dir/$entry\n";
                    if (is_dir($dir . '/' . $entry)) {
                        $list .= $this->scanDirectoryRecursively($dir . '/' . $entry);
                    }
                }
            }
            closedir($handle);
        }

        return $list;
    }
}
