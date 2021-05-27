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
use Akeneo\AssetManager\Domain\Query\Asset\FindCodesByIdentifiersInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

class SqlFindCodesByIdentifiersTest extends SqlIntegrationTestCase
{
    private FindCodesByIdentifiersInterface $query;

    public function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_codes_by_identifiers');
        $this->resetDB();
        $this->loadAssetFamilyDesigner();
        $this->loadAssets();
    }

    /**
     * @test
     */
    public function it_finds_asset_codes_given_their_identifiers()
    {
        $codes = $this->query->find(['designer_stark_fingerprint', 'designer_jacobs_fingerprint']);

        $this->assertEquals([
            'designer_stark_fingerprint' => 'starck',
            'designer_jacobs_fingerprint' => 'jacobs',
        ], $codes);

        $codes = $this->query->find(['unknown', 'designer_jacobs_fingerprint']);

        $this->assertEquals([
            'designer_jacobs_fingerprint' => 'jacobs',
        ], $codes);

        $codes = $this->query->find(['unknown']);

        $this->assertEmpty($codes);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamilyDesigner(): void
    {
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamily = AssetFamily::create(
            AssetFamilyIdentifier::fromString('designer'),
            [
                'fr_FR' => 'Concepteur',
                'en_US' => 'Designer',
            ],
            Image::createEmpty(),
            RuleTemplateCollection::empty()
        );
        $assetFamilyRepository->create($assetFamily);
    }

    public function loadAssets(): void
    {
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $designerIdentifier = AssetFamilyIdentifier::fromString('designer');

        $starkCode = AssetCode::fromString('starck');
        $starkIdentifier = AssetIdentifier::create('designer', 'stark', 'fingerprint');
        $assetRepository->create(
            Asset::create(
                $starkIdentifier,
                $designerIdentifier,
                $starkCode,
                ValueCollection::fromValues([])
            )
        );

        $jacobsCode = AssetCode::fromString('jacobs');
        $jacobsIdentifier = AssetIdentifier::create('designer', 'jacobs', 'fingerprint');
        $assetRepository->create(
            Asset::create(
                $jacobsIdentifier,
                $designerIdentifier,
                $jacobsCode,
                ValueCollection::fromValues([])
            )
        );
    }
}
