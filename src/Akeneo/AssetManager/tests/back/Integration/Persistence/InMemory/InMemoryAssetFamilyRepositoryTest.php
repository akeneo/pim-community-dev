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

use Akeneo\AssetManager\Common\Fake\InMemoryAssetFamilyRepository;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyNotFoundException;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryAssetFamilyRepositoryTest extends TestCase
{
    private InMemoryAssetFamilyRepository $assetFamilyRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->assetFamilyRepository = new InMemoryAssetFamilyRepository(
            new EventDispatcher()
        );
    }

    /**
     * @test
     */
    public function it_creates_an_asset_family_and_returns_it()
    {
        $identifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetFamily = AssetFamily::create($identifier, [], Image::createEmpty(), RuleTemplateCollection::empty());

        $this->assetFamilyRepository->create($assetFamily);

        $assetFamilyFound = $this->assetFamilyRepository->getByIdentifier($identifier);
        Assert::assertTrue($assetFamily->equals($assetFamilyFound));
    }

    /**
     * @test
     */
    public function it_returns_an_asset_family_with_case_insensitive_search()
    {
        $identifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetFamily = AssetFamily::create($identifier, [], Image::createEmpty(), RuleTemplateCollection::empty());

        $this->assetFamilyRepository->create($assetFamily);

        $identifier = AssetFamilyIdentifier::fromString('asset_FAMILY_identifier');
        $assetFamilyFound = $this->assetFamilyRepository->getByIdentifier($identifier);
        Assert::assertTrue($assetFamily->equals($assetFamilyFound));
    }

    /**
     * @test
     */
    public function it_throws_when_creating_an_asset_family_with_the_same_identifier()
    {
        $identifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetFamily = AssetFamily::create($identifier, [], Image::createEmpty(), RuleTemplateCollection::empty());
        $this->assetFamilyRepository->create($assetFamily);

        $this->expectException(\RuntimeException::class);
        $this->assetFamilyRepository->create($assetFamily);
    }

    /**
     * @test
     */
    public function it_updates_an_asset_family_and_returns_it()
    {
        $identifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetFamily = AssetFamily::create($identifier, [], Image::createEmpty(), RuleTemplateCollection::empty());
        $this->assetFamilyRepository->create($assetFamily);
        $assetFamily->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Styliste']));

        $this->assetFamilyRepository->update($assetFamily);

        $assetFamilyFound = $this->assetFamilyRepository->getByIdentifier($identifier);
        Assert::assertTrue($assetFamily->equals($assetFamilyFound));
    }

    /**
     * @test
     */
    public function it_returns_all_asset_families()
    {
        $designer = AssetFamily::create(AssetFamilyIdentifier::fromString('designer'), [], Image::createEmpty(), RuleTemplateCollection::empty());
        $brand = AssetFamily::create(AssetFamilyIdentifier::fromString('brand'), [], Image::createEmpty(), RuleTemplateCollection::empty());
        $this->assetFamilyRepository->create($designer);
        $this->assetFamilyRepository->create($brand);

        $assetFamilies = iterator_to_array($this->assetFamilyRepository->all());
        Assert::assertSame($designer, $assetFamilies[0]);
        Assert::assertSame($brand, $assetFamilies[1]);
    }

    /**
     * @test
     */
    public function it_tells_if_the_repository_has_the_asset_family()
    {
        $existentIdentifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $this->assetFamilyRepository->create(AssetFamily::create($existentIdentifier, [], Image::createEmpty(), RuleTemplateCollection::empty()));

        Assert::assertTrue($this->assetFamilyRepository->hasAssetFamily(
                AssetFamilyIdentifier::fromString('asset_family_identifier')
            )
        );
        Assert::assertTrue(
            $this->assetFamilyRepository->hasAssetFamily(
                AssetFamilyIdentifier::fromString('ASSET_FAMILY_IDENTIFIER'),
                false
            )
        );
        Assert::assertFalse(
            $this->assetFamilyRepository->hasAssetFamily(
                AssetFamilyIdentifier::fromString('ASSET_FAMILY_IDENTIFIER'),
                true
            )
        );

        Assert::assertFalse($this->assetFamilyRepository->hasAssetFamily(AssetFamilyIdentifier::fromString('other')));
    }

    /**
     * @test
     */
    public function it_throws_when_udpating_a_non_existing_asset_family()
    {
        $identifier = AssetFamilyIdentifier::fromString('asset_family_identifier');
        $assetFamily = AssetFamily::create($identifier, [], Image::createEmpty(), RuleTemplateCollection::empty());
        $this->assetFamilyRepository->create($assetFamily);
        $assetFamily->updateLabels(LabelCollection::fromArray(['fr_FR' => 'Styliste']));

        $this->assetFamilyRepository->update($assetFamily);

        $assetFamilyFound = $this->assetFamilyRepository->getByIdentifier($identifier);
        Assert::assertTrue($assetFamily->equals($assetFamilyFound));
    }

    /**
     * @test
     */
    public function it_throws_if_the_identifier_is_not_found()
    {
        $this->expectException(AssetFamilyNotFoundException::class);
        $this->assetFamilyRepository->getByIdentifier(
            AssetFamilyIdentifier::fromString('unknown_identifier')
        );
    }

    /**
     * @test
     */
    public function it_deletes_an_asset_family_given_an_identifier()
    {
        $identifier = AssetFamilyIdentifier::fromString('identifier');
        $assetFamily = AssetFamily::create(
            $identifier,
            ['en_US' => 'Designer', 'fr_FR' => 'Concepteur'],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);

        $this->assetFamilyRepository->deleteByIdentifier($identifier);

        $this->expectException(AssetFamilyNotFoundException::class);
        $this->assetFamilyRepository->getByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_throws_an_exception_if_it_tries_to_delete_an_unknown_asset_family()
    {
        $identifier = AssetFamilyIdentifier::fromString('unknown');

        $this->expectException(AssetFamilyNotFoundException::class);
        $this->assetFamilyRepository->deleteByIdentifier($identifier);
    }

    /**
     * @test
     */
    public function it_counts_the_total_of_asset_families()
    {
        $this->assertEquals(0, $this->assetFamilyRepository->count());

        $refOne = AssetFamily::create(AssetFamilyIdentifier::fromString('one'), ['en_US' => 'one'], Image::createEmpty(), RuleTemplateCollection::empty());
        $refTwo = AssetFamily::create(AssetFamilyIdentifier::fromString('two'), ['en_US' => 'two'], Image::createEmpty(), RuleTemplateCollection::empty());
        $refThree = AssetFamily::create(AssetFamilyIdentifier::fromString('three'), ['en_US' => 'three'], Image::createEmpty(), RuleTemplateCollection::empty());
        $this->assetFamilyRepository->create($refOne);
        $this->assetFamilyRepository->create($refTwo);
        $this->assetFamilyRepository->create($refThree);
        $this->assertEquals(3, $this->assetFamilyRepository->count());
    }
}
