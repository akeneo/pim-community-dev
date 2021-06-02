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

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryAssetRepositoryTest extends TestCase
{
    private InMemoryAssetRepository $assetRepository;

    public function setUp(): void
    {
        $this->assetRepository = new InMemoryAssetRepository(new EventDispatcher());
    }

    /**
     * @test
     */
    public function it_creates_a_asset_and_returns_it()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename('image_2.jpg')
            ->setKey('test/image_2.jpg');

        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('image_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($imageInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
                ),
            ])
        );

        $this->assetRepository->create($asset);

        $assetFound = $this->assetRepository->getByIdentifier($identifier);
        $this->assertTrue($asset->equals($assetFound));
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_existing_asset_with_same_identifier()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );
        $this->assetRepository->create($asset);

        $this->expectException(\RuntimeException::class);
        $this->assetRepository->create($asset);
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_existing_asset_with_same_entity_identifier_and_same_code()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );
        $this->assetRepository->create($asset);

        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );

        $this->expectException(\RuntimeException::class);
        $this->assetRepository->create($asset);
    }

    /**
     * @test
     */
    public function it_updates_a_asset_and_returns_it()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );
        $this->assetRepository->create($asset);

        $this->assetRepository->update($asset);
        $assetFound = $this->assetRepository->getByIdentifier($identifier);

        $this->assertTrue($asset->equals($assetFound));
    }

    /**
     * @test
     */
    public function it_throws_when_updating_a_non_existing_asset()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );

        $this->expectException(\RuntimeException::class);
        $this->assetRepository->update($asset);
    }

    /**
     * @test
     */
    public function it_counts_the_assets()
    {
        $this->assertEquals(0, $this->assetRepository->count());

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );

        $this->assetRepository->create($asset);

        $this->assertEquals(1, $this->assetRepository->count());

        $assetIdentifier = AssetCode::fromString('asset_identifier');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetIdentifier);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetIdentifier,
            ValueCollection::fromValues([])
        );

        $this->assetRepository->create($asset);

        $this->assertEquals(2, $this->assetRepository->count());
    }

    public function it_tells_if_it_has_a_asset_identifier()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );

        $this->assetRepository->create($asset);
        $this->assertTrue($this->assetRepository->hasAsset($identifier));
    }

    /**
     * @test
     */
    public function it_throws_if_the_code_is_not_found()
    {
        $this->expectException(AssetNotFoundException::class);
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('unknown_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $this->assetRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_throws_if_the_asset_family_identifier_is_not_found()
    {
        $this->expectException(AssetNotFoundException::class);
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('unknown_asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $this->assetRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_deletes_a_asset_by_code_and_entity_identifier()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );
        $this->assetRepository->create($asset);

        $this->assetRepository->deleteByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);

        $hasAsset = 0 !== $this->assetRepository->count();
        Assert::assertFalse($hasAsset, 'Expected asset to be removed, but was not');
    }

    /**
     * @test
     */
    public function it_throws_if_trying_to_delete_an_unknown_asset()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetCode = AssetCode::fromString('asset_code');
        $identifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );
        $this->assetRepository->create($asset);

        $unknownCode = AssetCode::fromString('unknown_code');

        $this->expectException(AssetNotFoundException::class);
        $this->assetRepository->deleteByAssetFamilyAndCode($assetFamilyIdentifier, $unknownCode);
    }

    /**
     * @test
     */
    public function it_counts_the_assets_by_asset_family()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $this->assertSame(0, $this->assetRepository->countByAssetFamily($assetFamilyIdentifier));

        $starck = Asset::create(
            AssetIdentifier::fromString('starck_designer'),
            $assetFamilyIdentifier,
            AssetCode::fromString('starck'),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('label_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Philippe Starck')
                ),
            ])
        );
        $this->assetRepository->create($starck);
        $this->assertSame(1, $this->assetRepository->countByAssetFamily($assetFamilyIdentifier));

        $bob = Asset::create(
            AssetIdentifier::fromString('bob_designer'),
            $assetFamilyIdentifier,
            AssetCode::fromString('bob'),
            ValueCollection::fromValues([
                Value::create(
                    AttributeIdentifier::fromString('label_designer_fingerprint'),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Bob')
                ),
            ])
        );
        $this->assetRepository->create($bob);
        $this->assertSame(2, $this->assetRepository->countByAssetFamily($assetFamilyIdentifier));
    }
}
