<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Integration\Transformation;

use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditAssetCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EditMediaFileValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ColorspaceOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResizeOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ResolutionOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ScaleOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation\ThumbnailOperation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\OperationCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Source;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Target;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Transformation;
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Infrastructure\Transformation\ComputeTransformationsExecutor;
use Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ComputeTransformationsExecutorTest extends KernelTestCase
{
    protected const FILENAME = __DIR__ . '/../../Common/TestFixtures/lardon.png';

    /** @var FixturesLoader */
    private $fixturesLoader;

    /** @var AbstractAttribute */
    private $targetImageAttribute;

    /**
     * @test
     */
    public function it_computes_a_transformation()
    {
        $executor = $this->getComputeTransformationsExecutor();
        $asset = $this->getAsset('starck');

        $executor->execute([$asset->getIdentifier()]);

        $asset = $this->getAsset('starck');
        $targetValue = $asset->findValue(
            ValueKey::create(
                $this->targetImageAttribute->getIdentifier(),
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        $this->assertNotNull($targetValue);
        $targetData = $targetValue->getData();
        $this->assertInstanceOf(FileData::class, $targetData);

        $this->assertEquals($targetData->normalize()['mimeType'], 'image/png');
        $this->assertEquals($targetData->normalize()['extension'], 'png');
        $this->assertStringEndsWith($targetData->normalize()['originalFilename'], 'lardon_computed.png');

        $file = $this->getFileDownloader()->get($targetData->getKey());
        $metadata = getimagesize($file->getRealPath());
        $this->assertEquals(300, $metadata[0]);
        $this->assertEquals(337, $metadata[1]);
        $this->assertEquals('image/png', $metadata['mime']);
    }

    public function setUp(): void
    {
        static::bootKernel(['debug' => false]);
        parent::setUp();
        $this->resetDB();
        $this->fixturesLoader = $this->getFixturesLoader();
        $this->loadFixtures();
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes(['main_image', 'target_image'])
            ->load();
        $mainImageAttribute = $fixtures['attributes']['main_image'];
        $this->targetImageAttribute = $fixtures['attributes']['target_image'];
        $designer = $fixtures['asset_family'];

        $this->setFamilyTransformations($designer, $mainImageAttribute, $this->targetImageAttribute);

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([])
            ->load();

        $this->setMediaFileValue('designer', 'starck', $mainImageAttribute);
    }

    private function get(string $service)
    {
        return self::$container->get($service);
    }

    private function setFamilyTransformations(
        AssetFamily $family,
        AbstractAttribute $source,
        AbstractAttribute $target
    ) {
        $updatedFamily = $family->withTransformationCollection(
            TransformationCollection::create([
                Transformation::create(
                    TransformationCode::fromString('code'),
                    Source::create(
                        $source,
                        ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('ecommerce')),
                        LocaleReference::noReference()
                    ),
                    Target::create(
                        $target,
                        ChannelReference::noReference(),
                        LocaleReference::noReference()
                    ),
                    OperationCollection::create([
                        ScaleOperation::create(['width' => 800, 'height' => 600]),
                        ColorspaceOperation::create(['colorspace' => 'grey']),
                        ResizeOperation::create(['width' => 400, 'height' => 450]),
                        ThumbnailOperation::create(['width' => 300]),
                        ResolutionOperation::create(['resolution-x' => 1000, 'resolution-y' => 1000, 'resolution-unit' => 'ppc']),
                    ]),
                    null,
                    '_computed',
                    new \DateTime()
                )
            ])
        );
        $this->getAssetFamilyRepository()->update($updatedFamily);
    }

    private function getAsset(string $assetCode): Asset
    {
        return $this->getAssetRepository()->getByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString('designer'),
            AssetCode::fromString($assetCode)
        );
    }

    private function setMediaFileValue(
        string $assetFamilyIdentifier,
        string $assetCode,
        MediaFileAttribute $attribute
    ): void {
        $file = new File(self::FILENAME);
        $storedFile = $this->getFileStorer()->store($file, Storage::FILE_STORAGE_ALIAS, false);

        $editValueCommand = new EditMediaFileValueCommand(
            $attribute,
            'ecommerce',
            null,
            $storedFile->getKey(),
            $storedFile->getOriginalFilename(),
            $storedFile->getSize(),
            $storedFile->getMimeType(),
            $storedFile->getExtension(),
            (new \DateTimeImmutable())->format(\DateTimeInterface::ISO8601)
        );
        $editAssetCommand = new EditAssetCommand(
            $assetFamilyIdentifier,
            $assetCode,
            [$editValueCommand]
        );
        ($this->getEditAssetHandler())($editAssetCommand);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function getFileStorer(): FileStorer
    {
        return $this->get('akeneo_file_storage.file_storage.file.file_storer');
    }

    private function getComputeTransformationsExecutor(): ComputeTransformationsExecutor
    {
        return $this->get('akeneo_assetmanager.infrastructure.transformation.compute_transformations.executor');
    }

    private function getAssetRepository(): AssetRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
    }

    private function getFileDownloader(): FileDownloader
    {
        return $this->get('Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader');
    }

    private function getFixturesLoader(): FixturesLoader
    {
        return $this->get('akeneoasset_manager.tests.helper.fixtures_loader');
    }

    private function getAssetFamilyRepository(): AssetFamilyRepositoryInterface
    {
        return $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
    }

    private function getEditAssetHandler(): EditAssetHandler
    {
        return $this->get('akeneo_assetmanager.application.asset.edit_asset_handler');
    }
}
