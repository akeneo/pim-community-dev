<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Query\Asset\FindIdentifiersByAssetFamilyAndCodesInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindIdentifiersByAssetFamilyAndCodesTest extends SqlIntegrationTestCase
{
    /** @var FindIdentifiersByAssetFamilyAndCodesInterface */
    private $findIdentifiersByAssetFamilyAndCodes;

    /** @var AssetIdentifier */
    private $starckIdentifier;

    /** @var AssetIdentifier */
    private $cocoIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->findIdentifiersByAssetFamilyAndCodes = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_identifiers_by_asset_family_and_codes');
        $this->resetDB();
        $this->loadAssetFamilyAndAssets();
    }

    /**
     * @test
     */
    public function it_finds_identifiers_of_assets_by_their_asset_family_and_codes()
    {
        $identifiers = $this->findIdentifiersByAssetFamilyAndCodes->find(
            AssetFamilyIdentifier::fromString('designer'),
            [
                AssetCode::fromString('starck'),
                AssetCode::fromString('coco'),
            ]
        );

        $this->assertCount(2, $identifiers);
        $this->assertContainsEquals($this->starckIdentifier->normalize(), $identifiers);
        $this->assertContainsEquals($this->cocoIdentifier->normalize(), $identifiers);

        $identifiers = $this->findIdentifiersByAssetFamilyAndCodes->find(
            AssetFamilyIdentifier::fromString('designer'),
            [
                AssetCode::fromString('coco'),
            ]
        );

        $this->assertCount(1, $identifiers);
        $this->assertContainsEquals($this->cocoIdentifier->normalize(), $identifiers);
        $this->assertNotContainsEquals($this->starckIdentifier->normalize(), $identifiers);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamilyAndAssets(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
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
        $assetFamilyRepository->create($assetFamily);

        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $starkCode = AssetCode::fromString('starck');
        $this->starckIdentifier = $assetRepository->nextIdentifier($assetFamilyIdentifier, $starkCode);
        $assetRepository->create(
            Asset::create(
                $this->starckIdentifier,
                $assetFamilyIdentifier,
                $starkCode,
                ValueCollection::fromValues([])
            )
        );

        $cocoCode = AssetCode::fromString('coco');
        $this->cocoIdentifier = $assetRepository->nextIdentifier($assetFamilyIdentifier, $cocoCode);
        $assetRepository->create(
            Asset::create(
                $this->cocoIdentifier,
                $assetFamilyIdentifier,
                $cocoCode,
                ValueCollection::fromValues([])
            )
        );
    }
}
