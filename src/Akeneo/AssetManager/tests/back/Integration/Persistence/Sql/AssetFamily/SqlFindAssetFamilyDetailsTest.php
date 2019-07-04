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
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyDetails;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyDetailsInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use PHPUnit\Framework\Assert;

class SqlFindAssetFamilyDetailsTest extends SqlIntegrationTestCase
{
    /** @var FindAssetFamilyDetailsInterface */
    private $findAssetFamilyDetails;

    public function setUp(): void
    {
        parent::setUp();

        $this->findAssetFamilyDetails = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_asset_family_details');
        $this->resetDB();
        $this->loadAssetFamily();
    }

    /**
     * @test
     */
    public function it_returns_null_when_there_is_no_result_for_the_given_identifier()
    {
        $result = $this->findAssetFamilyDetails->find(AssetFamilyIdentifier::fromString('unknown_asset_family'));
        Assert::assertNull($result);
    }

    /**
     * @test
     */
    public function it_finds_one_asset_family_by_its_identifier()
    {
        $entity = $this->findAssetFamilyDetails->find(AssetFamilyIdentifier::fromString('designer'));

        $designer = new AssetFamilyDetails();
        $designer->identifier = AssetFamilyIdentifier::fromString('designer');
        $designer->labels = LabelCollection::fromArray(['fr_FR' => 'Concepteur', 'en_US' => 'Designer']);

        $this->assertAssetFamilyItem($designer, $entity);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function loadAssetFamily(): void
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

    private function assertAssetFamilyItem(AssetFamilyDetails $expected, AssetFamilyDetails $actual): void
    {
        $this->assertTrue($expected->identifier->equals($actual->identifier), 'Asset family identifiers are not equal');
        $expectedLabels = $expected->labels->normalize();
        $actualLabels = $actual->labels->normalize();
        $this->assertEmpty(
            array_merge(
                array_diff($expectedLabels, $actualLabels),
                array_diff($actualLabels, $expectedLabels)
            ),
            'Labels for the asset family items are not the same'
        );
    }
}
