<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\InMemory;

use Akeneo\AssetManager\Common\Fake\InMemoryAssetFamilyRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryAssetRepository;
use Akeneo\AssetManager\Common\Fake\InMemoryFindIdentifiersByAssetFamilyAndCodes;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class InMemoryFindIdentifiersByAssetFamilyAndCodesTest extends TestCase
{
    /** @var InMemoryAssetRepository */
    private $assetRepository;

    /** @var InMemoryAssetFamilyRepository */
    private $assetFamilyRepository;

    /** @var InMemoryFindIdentifiersByAssetFamilyAndCodes */
    private $query;

    /** @var AssetFamilyIdentifier */
    private $starckIdentifier;

    /** @var AssetFamilyIdentifier */
    private $cocoIdentifier;

    public function setUp(): void
    {
        $this->assetRepository = new InMemoryAssetRepository(new EventDispatcher());
        $this->assetFamilyRepository = new InMemoryAssetFamilyRepository(new EventDispatcher());
        $this->query = new InMemoryFindIdentifiersByAssetFamilyAndCodes(
            $this->assetRepository
        );
    }

    /**
     * @test
     */
    public function it_finds_identifiers_of_assets_by_their_asset_family_and_codes()
    {
        $this->loadFixtures();

        $identifiers = $this->query->find(
            AssetFamilyIdentifier::fromString('designer'),
            [
                AssetCode::fromString('starck'),
                AssetCode::fromString('coco'),
            ]
        );

        $this->assertCount(2, $identifiers);
        $this->assertContainsEquals($this->starckIdentifier->normalize(), $identifiers);
        $this->assertContainsEquals($this->cocoIdentifier->normalize(), $identifiers);

        $identifiers = $this->query->find(
            AssetFamilyIdentifier::fromString('designer'),
            [
                AssetCode::fromString('coco'),
            ]
        );

        $this->assertCount(1, $identifiers);
        $this->assertContainsEquals($this->cocoIdentifier->normalize(), $identifiers);
        $this->assertNotContainsEquals($this->starckIdentifier->normalize(), $identifiers);
    }

    private function loadFixtures()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $this->assetFamilyRepository->create($assetFamily);

        $starkCode = AssetCode::fromString('starck');
        $this->starckIdentifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $starkCode);
        $this->assetRepository->create(
            Asset::create(
                $this->starckIdentifier,
                $assetFamilyIdentifier,
                $starkCode,
                ValueCollection::fromValues([])
            )
        );

        $cocoCode = AssetCode::fromString('coco');
        $this->cocoIdentifier = $this->assetRepository->nextIdentifier($assetFamilyIdentifier, $cocoCode);
        $this->assetRepository->create(
            Asset::create(
                $this->cocoIdentifier,
                $assetFamilyIdentifier,
                $cocoCode,
                ValueCollection::fromValues([])
            )
        );
    }
}
