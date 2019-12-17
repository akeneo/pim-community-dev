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
use Akeneo\AssetManager\Application\Asset\EditAsset\CommandFactory\EmptyValueCommand;
use Akeneo\AssetManager\Application\Asset\EditAsset\EditAssetHandler;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
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
use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\TransformationLabel;
use Akeneo\AssetManager\Domain\Model\AssetFamily\TransformationCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\ValueKey;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Infrastructure\Filesystem\Storage;
use Akeneo\AssetManager\Infrastructure\Symfony\Command\Installer\FixturesLoader;
use Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\Batch\Job\BatchStatus;
use Akeneo\Tool\Component\Batch\Model\JobExecution;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\FileStorage\File\FileStorer;
use PHPUnit\Framework\Assert;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class ComputeTransformationsTest extends TestCase
{
    protected const FILENAME = __DIR__ . '/../../Common/TestFixtures/lardon.png';

    /** @var FixturesLoader */
    private $fixturesLoader;

    /** @var AttributeIdentifier */
    private $targetAttributeIdentifier;

    /**
     * @test
     */
    public function it_applies_a_thumbnail_operation()
    {
        $this->setFamilyTransformations([ThumbnailOperation::create(['width' => 100])]);
        $asset = $this->getAsset('starck');
        $this->launchTransformationJob($asset->getIdentifier());

        $this->assertTransformationSuccess('starck', 100, 83);
    }

    /**
     * @test
     */
    public function it_applies_a_scale_operation()
    {
        $this->setFamilyTransformations([ScaleOperation::create(['ratio' => 50])]);
        $asset = $this->getAsset('starck');
        $this->launchTransformationJob($asset->getIdentifier());

        $this->assertTransformationSuccess('starck', 480, 400);
    }

    /**
     * @test
     */
    public function it_applies_a_resize_operation()
    {
        $this->setFamilyTransformations([ResizeOperation::create(['width' => 500, 'height' => 100])]);
        $asset = $this->getAsset('starck');
        $this->launchTransformationJob($asset->getIdentifier());

        $this->assertTransformationSuccess('starck', 500, 100);
    }

    /**
     * @test
     */
    public function it_applies_a_resolution_operation()
    {
        $this->setFamilyTransformations(
            [
                ResolutionOperation::create(
                    [
                        'resolution-unit' => 'ppi',
                        'resolution-x' => 72,
                        'resolution-y' => 72,
                    ]
                ),
            ]
        );
        $asset = $this->getAsset('starck');
        $this->launchTransformationJob($asset->getIdentifier());

        $this->assertTransformationSuccess('starck', 960, 800);
    }

    /**
     * @test
     */
    public function it_applies_a_colorspace_operation()
    {
        $this->setFamilyTransformations([ColorspaceOperation::create(['colorspace' => 'grey'])]);
        $asset = $this->getAsset('starck');
        $this->launchTransformationJob($asset->getIdentifier());

        $this->assertTransformationSuccess('starck', 960, 800);
    }

    /**
     * @test
     */
    public function it_skips_the_transformation_with_a_warning_if_the_source_file_is_missing()
    {
        $this->setFamilyTransformations([ThumbnailOperation::create(['width' => 100])]);
        $asset = $this->getAsset('starck');

        // empty the source value
        $sourceAttribute = self::$container->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute')
                                           ->getByCodeAndAssetFamilyIdentifier(
                                               AttributeCode::fromString('main_image'),
                                               AssetFamilyIdentifier::fromString('designer')
                                           );

        $editAssetCommand = new EditAssetCommand(
            'designer',
            'starck',
            [
                new EmptyValueCommand(
                    $sourceAttribute,
                    'ecommerce',
                    null
                ),
            ]
        );
        $violations = static::$container->get('validator')->validate($editAssetCommand);
        Assert::assertEmpty($violations);
        ($this->getEditAssetHandler())($editAssetCommand);

        $this->launchTransformationJob($asset->getIdentifier());

        $lastExecution = $this->getLastExecution();
        Assert::assertsame(BatchStatus::COMPLETED, $lastExecution->getStatus()->getValue());

        $warnings = $this->getWarnings($lastExecution);
        Assert::assertSame(
            [
                'Cannot apply transformation "code" for asset "starck": The source file for attribute "main_image" is missing',
            ],
            $warnings
        );
    }

    /**
     * @test
     */
    public function it_skips_the_transformation_with_a_warning_if_the_asset_is_missing()
    {
        $this->setFamilyTransformations([ThumbnailOperation::create(['width' => 100])]);
        $asset = $this->getAsset('starck');
        $assetIdentifier = $asset->getIdentifier()->__toString();

        $this->getAssetRepository()->deleteByAssetFamilyAndCode(
            $asset->getAssetFamilyIdentifier(),
            $asset->getCode()
        );
        $this->launchTransformationJob($asset->getIdentifier());

        $lastExecution = $this->getLastExecution();
        Assert::assertsame(BatchStatus::COMPLETED, $lastExecution->getStatus()->getValue());

        $warnings = $this->getWarnings($lastExecution);
        Assert::assertSame(
            [sprintf('Asset %s does not exist', $assetIdentifier)],
            $warnings
        );
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixturesLoader = $this->get('akeneoasset_manager.tests.helper.fixtures_loader');
        $this->resetDB();
        $this->loadFixtures();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadFixtures(): void
    {
        $fixtures = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes(['main_image', 'target_image'])
            ->load();
        $mainImageAttribute = $fixtures['attributes']['main_image'];
        $this->targetAttributeIdentifier = $fixtures['attributes']['target_image']->getIdentifier();

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([])
            ->load();

        $this->setMediaFileValue('designer', 'starck', $mainImageAttribute);
    }

    private function assertTransformationSuccess(string $assetCode, int $width, int $height): void
    {
        $lastExecution = $this->getLastExecution();
        Assert::assertNotNull($lastExecution);
        Assert::assertSame(BatchStatus::COMPLETED, $lastExecution->getStatus()->getValue());
        /** @var StepExecution $stepExecution */
        $stepExecution = $lastExecution->getStepExecutions()->first();
        Assert::assertSame(1, $stepExecution->getSummaryInfo('transformations'));

        $asset = $this->getAsset($assetCode);
        $targetValue = $asset->findValue(
            ValueKey::create(
                $this->targetAttributeIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference()
            )
        );

        Assert::assertNotNull($targetValue);
        $targetData = $targetValue->getData();
        Assert::assertInstanceOf(FileData::class, $targetData);

        $normalized = $targetData->normalize();
        Assert::assertSame('image/png', $normalized['mimeType']);
        Assert::assertSame('png', $normalized['extension']);
        Assert::assertSame('lardon_computed.png', $targetData->getOriginalFilename());

        $file = $this->getFileDownloader()->get($targetData->getKey());
        $metadata = getimagesize($file->getRealPath());
        Assert::assertSame($width, $metadata[0]);
        Assert::assertSame($height, $metadata[1]);
        Assert::assertSame('image/png', $metadata['mime']);
    }

    private function setFamilyTransformations(
        array $operations
    ) {
        $repo = $this->getAssetFamilyRepository();
        $family = $repo->getByIdentifier(AssetFamilyIdentifier::fromString('designer'));

        $updatedFamily = $family->withTransformationCollection(
            TransformationCollection::create(
                [
                    Transformation::create(
                        TransformationLabel::fromString('code'),
                        Source::createFromNormalized(
                            [
                                'attribute' => 'main_image',
                                'channel' => 'ecommerce',
                                'locale' => null,
                            ]
                        ),
                        Target::createFromNormalized(
                            [
                                'attribute' => 'target_image',
                                'channel' => null,
                                'locale' => null,
                            ]
                        ),
                        OperationCollection::create($operations),
                        null,
                        '_computed',
                        new \DateTimeImmutable()
                    ),
                ]
            )
        );
        $repo->update($updatedFamily);
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
        $violations = static::$container->get('validator')->validate($editAssetCommand);
        Assert::assertEmpty($violations);

        ($this->getEditAssetHandler())($editAssetCommand);
    }

    private function resetDB(): void
    {
        static::$container->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function getFileStorer(): FileStorer
    {
        return static::$container->get('akeneo_file_storage.file_storage.file.file_storer');
    }

    private function getAssetRepository(): AssetRepositoryInterface
    {
        return static::$container->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
    }

    private function getFileDownloader(): FileDownloader
    {
        return static::$container->get('Akeneo\AssetManager\Infrastructure\Transformation\FileDownloader');
    }

    private function getAssetFamilyRepository(): AssetFamilyRepositoryInterface
    {
        return static::$container->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
    }

    private function getEditAssetHandler(): EditAssetHandler
    {
        return static::$container->get('akeneo_assetmanager.application.asset.edit_asset_handler');
    }

    private function launchTransformationJob(AssetIdentifier $assetIdentifier): void
    {
        static::$container->get(
            'akeneo_assetmanager.infrastructure.job.compute_transformations_from_asset_identifiers_launcher'
        )->launch([$assetIdentifier]);

        $jobLauncher = static::$container->get('akeneo_integration_tests.launcher.job_launcher');
        Assert::assertTrue($jobLauncher->hasJobInQueue());
        $jobLauncher->launchConsumerOnce();
    }

    private function getLastExecution(): ?JobExecution
    {
        /** @var JobInstance $jobInstance */
        $jobInstance = static::$container->get('akeneo_batch.job.job_instance_repository')->findOneByIdentifier(
            'asset_manager_compute_transformations'
        );

        return $jobInstance->getJobExecutions()->last();
    }

    private function getWarnings(JobExecution $jobExecution): array
    {
        $warnings = [];
        foreach ($jobExecution->getStepExecutions() as $stepExecution) {
            foreach ($stepExecution->getWarnings() as $warning) {
                $warnings[] = $warning->getReason();
            }
        }

        return $warnings;
    }
}
