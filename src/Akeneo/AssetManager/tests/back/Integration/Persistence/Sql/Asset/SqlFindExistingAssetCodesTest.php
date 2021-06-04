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

use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindExistingAssetCodesInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindExistingAssetCodesTest extends SqlIntegrationTestCase
{
    private FindExistingAssetCodesInterface $existingAssetCodes;

    private AssetIdentifier $assetIdentifier;

    public function setUp(): void
    {
        parent::setUp();

        $this->existingAssetCodes = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_existing_asset_codes');
        $this->resetDB();

        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_returns_the_asset_codes_found()
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString('designer');
        $expectedAssetCodes = ['jacobs', 'starck'];

        $assetCodes = $this->existingAssetCodes->find($assetFamilyIdentifier, ['Coco', 'starck', 'jacobs']);
        $this->assertEquals($expectedAssetCodes, $assetCodes);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures(): void
    {
        $this->fixturesLoader
            ->assetFamily('designer')
            ->load();

        $this->fixturesLoader
            ->asset('designer', 'starck')
            ->withValues([
                'label' => [
                    [
                        'channel' => null,
                        'locale' => 'fr_FR',
                        'data' => 'Philippe Starck',
                    ]
                ]
            ])
            ->load();

        $this->fixturesLoader
            ->asset('designer', 'jacobs')
            ->withValues([
                'label' => [
                    [
                        'channel' => null,
                        'locale' => 'fr_FR',
                        'data' => 'Marc Jacobs',
                    ]
                ]
            ])
            ->load();
    }
}
