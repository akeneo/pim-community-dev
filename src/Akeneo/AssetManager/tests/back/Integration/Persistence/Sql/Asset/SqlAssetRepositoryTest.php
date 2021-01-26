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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Event\AssetCreatedEvent;
use Akeneo\AssetManager\Domain\Event\AssetDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetFamilyAssetsDeletedEvent;
use Akeneo\AssetManager\Domain\Event\AssetUpdatedEvent;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\FileData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\MediaLinkData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\TextData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\ChannelIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetNotFoundException;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use AkeneoEnterprise\Test\IntegrationTestsBundle\EventDispatcher\EventDispatcherMock;
use Doctrine\DBAL\DBALException;
use PHPUnit\Framework\Assert;

class SqlAssetRepositoryTest extends SqlIntegrationTestCase
{
    /** @var EventDispatcherMock */
    private $eventDispatcherMock;

    /** @var AssetRepositoryInterface */
    private $repository;

    /** @var AssetFamilyRepositoryInterface */
    private $assetFamilyRepository;

    /** @var array */
    private $fixturesDesigner;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->eventDispatcherMock = $this->get('event_dispatcher');
        $this->eventDispatcherMock->reset();

        $this->resetDB();
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_creates_an_asset_with_no_values_and_returns_it()
    {
        $assetCode = AssetCode::fromString('starck');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );

        $this->assertNotEmpty($asset->getRecordedEvents());
        $this->repository->create($asset);

        $this->eventDispatcherMock->assertEventDispatched(AssetCreatedEvent::class);
        $this->assertEmpty($asset->getRecordedEvents());

        $assetFound = $this->repository->getByIdentifier($identifier);
        $this->assertSame($asset->normalize(), $assetFound->normalize());
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_existing_asset_with_same_entity_identifier_and_same_code()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetCode = AssetCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );

        $this->repository->create($asset);
        $this->eventDispatcherMock->reset();

        $assetCode = AssetCode::fromString('starck');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );

        $this->expectException(DBALException::class);
        $this->repository->create($asset);
        $this->eventDispatcherMock->assertNoEventDispatched();
    }

    /**
     * @test
     */
    public function it_creates_a_asset_with_no_values_and_finds_it_by_asset_family_and_asset_code()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetCode = AssetCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );

        $this->repository->create($asset);

        $assetFound = $this->repository->getByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);
        $this->assertEquals($asset->normalize(), $assetFound->normalize());
    }

    /**
     * @test
     */
    public function it_creates_a_asset_with_values_and_returns_it()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetCode = AssetCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $fileInfo = new FileInfo();
        $fileInfo
            ->setOriginalFilename('image_1.jpg')
            ->setKey('test/image_1.jpg')
            ->setSize(1024)
            ->setMimeType('image/jpeg')
            ->setExtension('jpg');

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
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $assetFamily->getAttributeAsMainMediaReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($imageInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['name']->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Philippe Stark')
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['main_image']->getIdentifier(),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($fileInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
                )
            ])
        );

        $this->repository->create($asset);

        $assetFound = $this->repository->getByIdentifier($identifier);
        $this->assertEquals($asset->normalize(), $assetFound->normalize());

        $assetFound = $this->repository->getByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);
        $this->assertEquals($asset->normalize(), $assetFound->normalize());
    }

    /**
     * @test
     */
    public function it_throws_when_creating_a_asset_with_the_same_identifier()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetCode = AssetCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );
        $this->repository->create($asset);

        $this->expectException(DBALException::class);
        $this->repository->create($asset);
    }

    /**
     * @test
     */
    public function it_updates_a_asset_and_returns_it()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetCode = AssetCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $fileInfo = new FileInfo();
        $fileInfo
            ->setOriginalFilename('image_1.jpg')
            ->setKey('test/image_1.jpg');

        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['name']->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('An old description')
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['main_image']->getIdentifier(),
                    ChannelReference::fromChannelIdentifier(ChannelIdentifier::fromCode('mobile')),
                    LocaleReference::noReference(),
                    FileData::createFromFileinfo($fileInfo, \DateTimeImmutable::createFromFormat(\DateTimeImmutable::ISO8601, '2019-11-22T15:16:21+0000'))
                ),
                Value::create(
                    $this->fixturesDesigner['attributes']['website']->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::noReference(),
                    MediaLinkData::fromString('http://my-dam.com/assets/house_255311/500x500')
                )
            ])
        );
        $this->repository->create($asset);
        $this->eventDispatcherMock->reset();

        $valueToUpdate = Value::create(
            $this->fixturesDesigner['attributes']['name']->getIdentifier(),
            ChannelReference::noReference(),
            LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
            TextData::fromString('A completely new and updated description')
        );
        $asset->setValue($valueToUpdate);
        $this->assertNotEmpty($asset->getRecordedEvents());
        $this->repository->update($asset);

        $this->eventDispatcherMock->assertEventDispatched(AssetUpdatedEvent::class);
        $this->assertEmpty($asset->getRecordedEvents());
        $assetFound = $this->repository->getByIdentifier($identifier);
        $this->assertEquals($asset->normalize(), $assetFound->normalize());
    }

    /**
     * @test
     */
    public function it_replaces_asset_value_codes_by_their_identifiers_when_creating_a_asset()
    {
        // Create the brand we will link
        $assetFamilyIdentifierBrand = AssetFamilyIdentifier::fromString('brand');
        $brandCode = AssetCode::fromString('ikea');
        $brandIdentifier = $this->repository->nextIdentifier($assetFamilyIdentifierBrand, $brandCode);
        $asset = Asset::create(
            $brandIdentifier,
            $assetFamilyIdentifierBrand,
            $brandCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($asset);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $assetCode = AssetCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('en_US')),
                    TextData::fromString('Starck')
                ),
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Starck')
                ),
            ])
        );
        $this->repository->create($asset);
        $assetFound = $this->repository->getByIdentifier($identifier);
        $this->assertEquals($asset->normalize(), $assetFound->normalize());
    }

    /**
     * @test
     */
    public function it_counts_the_assets()
    {
        $this->assertEquals(0, $this->repository->count());
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');

        $assetCode = AssetCode::fromString('asset_identifier');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );

        $this->repository->create($asset);

        $this->assertEquals(1, $this->repository->count());

        $assetCode = AssetCode::fromString('asset_identifier2');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );

        $this->repository->create($asset);

        $this->assertEquals(2, $this->repository->count());
    }

    public function it_retrieve_the_next_identifier()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('starck');
        $nextIdentifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $this->assertNotEmpty($nextIdentifier);
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(AssetNotFoundException::class);

        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('unknown_identifier');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);

        $this->repository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_deletes_assets_by_asset_family_identifier()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('starck');

        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($asset);

        $assetCode = AssetCode::fromString('dyson');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($asset);

        $assetFamilyIdentifierBrand = AssetFamilyIdentifier::fromString('brand');
        $assetCode = AssetCode::fromString('bar');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifierBrand, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifierBrand,
            $assetCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($asset);

        Assert::assertEquals(3, $this->repository->count());

        $this->repository->deleteByAssetFamily($assetFamilyIdentifier);
        $this->eventDispatcherMock->assertEventDispatched(AssetFamilyAssetsDeletedEvent::class);
        Assert::assertEquals(1, $this->repository->count());
    }

    /**
     * @test
     */
    public function it_mass_deletes_assets_by_asset_family_identifier_and_codes()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCodesToDelete = [AssetCode::fromString('starck'), AssetCode::fromString('dyson')];
        $assetCodeToKeep = AssetCode::fromString('michel');

        foreach ([...$assetCodesToDelete, $assetCodeToKeep] as $assetCode) {
            $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
            $asset = Asset::create(
                $identifier,
                $assetFamilyIdentifier,
                $assetCode,
                ValueCollection::fromValues([])
            );
            $this->repository->create($asset);
        }

        Assert::assertEquals(3, $this->repository->count());

        $this->repository->deleteByAssetFamilyAndCodes($assetFamilyIdentifier, $assetCodesToDelete);
        $this->eventDispatcherMock->assertEventDispatched(AssetDeletedEvent::class);
        Assert::assertEquals(1, $this->repository->count());
    }

    /**
     * @test
     */
    public function it_deletes_a_asset_by_code_and_entity_identifier()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetCode = AssetCode::fromString('starck');
        $identifier = $this->repository->nextIdentifier($assetFamilyIdentifier, $assetCode);
        $asset = Asset::create(
            $identifier,
            $assetFamilyIdentifier,
            $assetCode,
            ValueCollection::fromValues([])
        );
        $this->repository->create($asset);

        $this->repository->deleteByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);

        $this->eventDispatcherMock->assertEventDispatched(AssetDeletedEvent::class);
        $this->expectException(AssetNotFoundException::class);
        $this->repository->deleteByAssetFamilyAndCode($assetFamilyIdentifier, $assetCode);
    }

    /**
     * @test
     */
    public function it_throws_if_trying_to_delete_an_unknown_asset()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $unknownCode = AssetCode::fromString('unknown_code');

        $this->expectException(AssetNotFoundException::class);
        $this->repository->deleteByAssetFamilyAndCode($assetFamilyIdentifier, $unknownCode);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->fixturesLoader
            ->assetFamily('brand')
            ->load();

        $this->fixturesDesigner = $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes(['name', 'main_image', 'brand', 'brands', 'website'])
            ->load();
    }

    /**
     * @test
     */
    public function it_counts_the_assets_by_asset_family()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $this->assertSame(0, $this->repository->countByAssetFamily($assetFamilyIdentifier));

        $assetFamily = $this->assetFamilyRepository->getByIdentifier($assetFamilyIdentifier);
        $starck = Asset::create(
            $this->repository->nextIdentifier($assetFamilyIdentifier, AssetCode::fromString('starck')),
            $assetFamilyIdentifier,
            AssetCode::fromString('starck'),
            ValueCollection::fromValues([
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Philippe Starck')
                ),
            ])
        );
        $this->repository->create($starck);
        $this->assertSame(1, $this->repository->countByAssetFamily($assetFamilyIdentifier));

        $bob = Asset::create(
            $this->repository->nextIdentifier($assetFamilyIdentifier, AssetCode::fromString('bob')),
            $assetFamilyIdentifier,
            AssetCode::fromString('bob'),
            ValueCollection::fromValues([
                Value::create(
                    $assetFamily->getAttributeAsLabelReference()->getIdentifier(),
                    ChannelReference::noReference(),
                    LocaleReference::fromLocaleIdentifier(LocaleIdentifier::fromCode('fr_FR')),
                    TextData::fromString('Bob')
                ),
            ])
        );
        $this->repository->create($bob);
        $this->assertSame(2, $this->repository->countByAssetFamily($assetFamilyIdentifier));
    }
}
