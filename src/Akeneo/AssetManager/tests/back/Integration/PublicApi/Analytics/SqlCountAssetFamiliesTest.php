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

namespace Akeneo\AssetManager\Integration\PublicApi\Analytics;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Infrastructure\PublicApi\Analytics\SqlCountAssetFamilies;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlCountAssetFamiliesTest extends SqlIntegrationTestCase
{
    private SqlCountAssetFamilies $countAssetFamilies;

    public function setUp(): void
    {
        parent::setUp();

        $this->countAssetFamilies = $this->get('akeneo_assetmanager.infrastructure.persistence.query.analytics.count_asset_families');
        $this->resetDB();
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    /**
     * @test
     */
    public function it_returns_the_number_of_asset_families_without_warning()
    {
        $this->loadAssetFamilies(2);
        $volume = $this->countAssetFamilies->fetch();
        $this->assertEquals('2', $volume->getVolume());
    }

    private function loadAssetFamilies(int $numberOfAssetFamiliestoLoad): void
    {
        for ($i = 0; $i < $numberOfAssetFamiliestoLoad; $i++) {
            $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
            $assetFamily = AssetFamily::create(
                AssetFamilyIdentifier::fromString(sprintf('%d', $i)),
                [],
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            );
            $assetFamilyRepository->create($assetFamily);
        }
    }
}
