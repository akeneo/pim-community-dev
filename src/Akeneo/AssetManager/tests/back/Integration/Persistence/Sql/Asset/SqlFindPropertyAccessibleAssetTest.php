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

namespace Akeneo\AssetManager\Integration\Persistence\Sql\Asset;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\FindPropertyAccessibleAssetInterface;
use Akeneo\AssetManager\Domain\Query\Asset\PropertyAccessibleAsset;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class SqlFindPropertyAccessibleAssetTest extends SqlIntegrationTestCase
{
    /** @var FindPropertyAccessibleAssetInterface */
    private $findPropertyAccessibleAsset;

    public function setUp(): void
    {
        parent::setUp();

        $this->findPropertyAccessibleAsset = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_property_accessible_asset');
        $this->resetDB();
        $this->loadFixtures();
    }

    /**
     * @test
     */
    public function it_finds_accessible_asset()
    {
        $propertyAccessibleAsset = $this->findPropertyAccessibleAsset->find(
            AssetFamilyIdentifier::fromString('designer'),
            AssetCode::fromString('nesquick_cereales')
        );

        $this->assertInstanceOf(PropertyAccessibleAsset::class, $propertyAccessibleAsset);
        $this->assertEquals('michel@gmail.com', $propertyAccessibleAsset->getValue('email'));
        $this->assertEquals('Nesquick', $propertyAccessibleAsset->getValue('name-fr_FR'));
    }

    /**
     * @test
     */
    public function it_could_not_find_accessible_asset()
    {
        $propertyAccessibleAsset = $this->findPropertyAccessibleAsset->find(
            AssetFamilyIdentifier::fromString('designer'),
            AssetCode::fromString('test')
        );

        $this->assertNull($propertyAccessibleAsset);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadFixtures()
    {
        $this->fixturesLoader
            ->assetFamily('designer')
            ->withAttributes([
                'name',
                'email'
            ])
            ->load();

        $this->fixturesLoader
            ->asset('designer', 'nesquick_cereales')
            ->withValues([
                'name' => [
                    [
                        'channel' => null,
                        'locale'  => 'fr_FR',
                        'data'    => 'Nesquick'
                    ]
                ],
                'email' => [
                    [
                        'channel' => null,
                        'locale' => null,
                        'data' => 'michel@gmail.com',
                    ]
                ]
             ])
            ->load();
    }
}
