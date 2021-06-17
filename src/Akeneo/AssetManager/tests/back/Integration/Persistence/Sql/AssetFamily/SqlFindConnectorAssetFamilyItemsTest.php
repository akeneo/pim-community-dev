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
use Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention\NullNamingConvention;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\AssetFamilyQuery;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorAssetFamily;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\ConnectorTransformationCollection;
use Akeneo\AssetManager\Domain\Query\AssetFamily\Connector\FindConnectorAssetFamilyItemsInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;

class SqlFindConnectorAssetFamilyItemsTest extends SqlIntegrationTestCase
{
    private AssetFamilyRepositoryInterface $assetFamilyRepository;

    private FindConnectorAssetFamilyItemsInterface $findConnectorAssetFamilyItems;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $this->findConnectorAssetFamilyItems = $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_connector_asset_family_items');
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_finds_connector_asset_family_items_without_search_after()
    {
        $assetFamilies = [];

        for ($i = 1; $i <= 3; $i++) {
            $assetFamily = $this->createAssetFamily(sprintf('asset_family_%s', $i));
            $assetFamilies[] = new ConnectorAssetFamily(
                $assetFamily->getIdentifier(),
                LabelCollection::fromArray(['en_US' => sprintf('asset_family_%s', $i)]),
                Image::createEmpty(),
                [],
                new ConnectorTransformationCollection([]),
                new NullNamingConvention(),
                AttributeCode::fromString('media')
            );
        }

        $findAssetFamiliesQuery = AssetFamilyQuery::createPaginatedQuery(3, null);
        $foundAssetFamilies = $this->findConnectorAssetFamilyItems->find($findAssetFamiliesQuery);

        $normalizedAssetFamilies = [];
        foreach ($assetFamilies as $assetFamily) {
            $normalizedAssetFamilies[] = $assetFamily->normalize();
        }

        $normalizedFoundAssetFamilies = [];
        foreach ($foundAssetFamilies as $assetFamily) {
            $normalizedFoundAssetFamilies[] = $assetFamily->normalize();
        }

        $this->assertEquals($normalizedAssetFamilies, $normalizedFoundAssetFamilies);
    }

    /**
     * @test
     */
    public function it_finds_connector_asset_families_after_identifier()
    {
        $assetFamilies = [];

        for ($i = 1; $i <= 7; $i++) {
            $assetFamily = $this->createAssetFamily(sprintf('asset_family_%s', $i));
            $assetFamilies[] = new ConnectorAssetFamily(
                $assetFamily->getIdentifier(),
                LabelCollection::fromArray(['en_US' => sprintf('asset_family_%s', $i)]),
                Image::createEmpty(),
                [],
                new ConnectorTransformationCollection([]),
                new NullNamingConvention(),
                AttributeCode::fromString('media')
            );
        }

        $searchAfterIdentifier = AssetFamilyIdentifier::fromString('asset_family_3');
        $findAssetFamiliesQuery = AssetFamilyQuery::createPaginatedQuery(3, $searchAfterIdentifier);
        $foundAssetFamilies = $this->findConnectorAssetFamilyItems->find($findAssetFamiliesQuery);

        $normalizedAssetFamilies = [];
        foreach ($assetFamilies as $assetFamily) {
            $normalizedAssetFamilies[] = $assetFamily->normalize();
        }

        $normalizedFoundAssetFamilies = [];
        foreach ($foundAssetFamilies as $assetFamily) {
            $normalizedFoundAssetFamilies[] = $assetFamily->normalize();
        }

        $this->assertEquals(array_slice($normalizedAssetFamilies, 3, 3), $normalizedFoundAssetFamilies);
    }

    /**
     * @test
     */
    public function it_returns_an_empty_array_if_no_asset_families_found()
    {
        $findAssetFamiliesQuery = AssetFamilyQuery::createPaginatedQuery(3, null);
        $foundAssetFamilies = $this->findConnectorAssetFamilyItems->find($findAssetFamiliesQuery);

        $this->assertSame([], $foundAssetFamilies);
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function createAssetFamily(string $rawIdentifier): AssetFamily
    {
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($rawIdentifier);

        $imageInfo = new FileInfo();
        $imageInfo
            ->setOriginalFilename(sprintf('image_%s', $rawIdentifier))
            ->setKey(sprintf('test/image_%s.jpg', $rawIdentifier));

        $assetFamily = AssetFamily::create(
            $assetFamilyIdentifier,
            ['en_US' => $rawIdentifier],
            Image::fromFileInfo($imageInfo),
            RuleTemplateCollection::empty()
        );

        $this->assetFamilyRepository->create($assetFamily);

        return $assetFamily;
    }
}
