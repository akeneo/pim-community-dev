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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\AssetFamily;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyExistsInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlAssetFamilyExistsTest extends SqlIntegrationTestCase
{
    private AssetFamilyExistsInterface $assetFamilyExists;

    public function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyExists = $this->get('akeneo_assetmanager.infrastructure.persistence.query.asset_family_exists');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_tells_if_there_is_a_corresponding_asset_identifier()
    {
        $this->loadAssetFamilyDesigner();
        $this->assertTrue($this->assetFamilyExists->withIdentifier(AssetFamilyIdentifier::fromString('designer')));
        $this->assertFalse($this->assetFamilyExists->withIdentifier(AssetFamilyIdentifier::fromString('manufacturer')));
    }

    /**
     * @test
     */
    public function it_can_be_case_sensitive_or_insensitive()
    {
        $this->loadAssetFamilyDesigner();
        $this->assertTrue($this->assetFamilyExists->withIdentifier(AssetFamilyIdentifier::fromString('designer')));

        $this->assertTrue($this->assetFamilyExists->withIdentifier(AssetFamilyIdentifier::fromString('DESIGNER'), false));
        $this->assertFalse($this->assetFamilyExists->withIdentifier(AssetFamilyIdentifier::fromString('DESIGNER'), true));
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
}
