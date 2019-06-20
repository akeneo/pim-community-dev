<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\CLI;

use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\AssetCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\AssetData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshAssetLinksTest extends SqlIntegrationTestCase
{
    /** @var AttributeIdentifier */
    private $currentAttributeIdentifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_refreshes_a_asset_having_a_link_to_a_asset_that_has_been_removed(): void
    {
        $this->loadAssetsForAssetFamily('brand', ['kartell']);
        $this->loadAssetsForAssetFamily('designer', ['stark']);
        $this->createAttributeAssetSingleLinkOnAssetFamily('brand', 'designer');
        $this->linkAssetFromTo('kartell', 'stark');
        $this->removeAsset('designer', 'stark');
        $this->assertTrue($this->IsAssetHavingValue('kartell', 'stark'));

        $this->runRefreshAssetsCommand();
        $this->assertFalse($this->IsAssetHavingValue('kartell', 'stark'));
    }

    /**
     * @test
     */
    public function it_refreshes_a_asset_having_a_one_link_to_a_asset_that_has_been_removed(): void
    {
        $this->loadAssetsForAssetFamily('brand', ['kartell']);
        $this->loadAssetsForAssetFamily('designer', ['stark', 'dyson']);
        $this->createAttributeAssetMultipleLinkOnAssetFamily('brand', 'designer');
        $this->linkMultipleAssetsFromTo('kartell', ['stark', 'dyson']);
        $this->removeAsset('designer', 'stark');
        $this->assertTrue($this->IsAssetHavingValue('kartell', 'stark'));
        $this->assertTrue($this->IsAssetHavingValue('kartell', 'dyson'));

        $this->runRefreshAssetsCommand();

        $this->assertFalse($this->IsAssetHavingValue('kartell', 'stark'));
        $this->assertTrue($this->IsAssetHavingValue('kartell', 'dyson'));
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function runRefreshAssetsCommand(): void
    {
        $application = new Application($this->testKernel);
        $command = $application->find('akeneo:asset-manager:refresh-assets');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--all'   => true,
        ]);
    }

    private function loadAssetFamily(string $assetFamilyIdentifier)
    {
        /** @var AssetFamilyRepositoryInterface $assetFamilyRepository */
        $assetFamilyRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset_family');
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($assetFamilyIdentifier);
        $assetFamilyRepository->create(
            AssetFamily::create(
                $assetFamilyIdentifier,
                [],
                Image::createEmpty()
            )
        );
    }

    private function loadAssetsForAssetFamily(string $assetFamilyIdentifier, array $assetCodes): void
    {
        $this->loadAssetFamily($assetFamilyIdentifier);
        foreach ($assetCodes as $assetCode) {
            /** @var AssetRepositoryInterface $assetRepository */
            $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
            $assetRepository->create(
                Asset::create(
                    AssetIdentifier::fromString($assetCode),
                    AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
                    AssetCode::fromString($assetCode),
                    ValueCollection::fromValues([])
                )
            );
        }
    }

    private function createAttributeAssetSingleLinkOnAssetFamily(
        string $fromAssetFamilyIdentifier,
        string $toAssetFamily
    ): void {
        $this->currentAttributeIdentifier = AttributeIdentifier::fromString('favorite_designer');
        $optionAttribute = AssetAttribute::create(
            $this->currentAttributeIdentifier,
            AssetFamilyIdentifier::fromString($fromAssetFamilyIdentifier),
            AttributeCode::fromString('favorite_designer'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AssetFamilyIdentifier::fromString($toAssetFamily)
        );

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function createAttributeAssetMultipleLinkOnAssetFamily(
        string $fromAssetFamilyIdentifier,
        string $toAssetFamily
    ): void {
        $this->currentAttributeIdentifier = AttributeIdentifier::fromString('favorite_designer');
        $optionAttribute = AssetCollectionAttribute::create(
            $this->currentAttributeIdentifier,
            AssetFamilyIdentifier::fromString($fromAssetFamilyIdentifier),
            AttributeCode::fromString('favorite_designer'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false),
            AssetFamilyIdentifier::fromString($toAssetFamily)
        );

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function linkAssetFromTo(string $fromAsset, string $toAsset): void
    {
        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $fromAsset = $assetRepository->getByIdentifier(AssetIdentifier::fromString($fromAsset));
        $fromAsset->setValue(
            Value::create(
                $this->currentAttributeIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                AssetData::createFromNormalize($toAsset)
            )
        );
        $assetRepository->update($fromAsset);
    }

    private function linkMultipleAssetsFromTo(string $fromAsset, array $toAssets): void
    {
        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $fromAsset = $assetRepository->getByIdentifier(AssetIdentifier::fromString($fromAsset));
        $fromAsset->setValue(
            Value::create(
                $this->currentAttributeIdentifier,
                ChannelReference::noReference(),
                LocaleReference::noReference(),
                AssetCollectionData::createFromNormalize($toAssets)
            )
        );
        $assetRepository->update($fromAsset);
    }

    private function removeAsset(string $assetFamilyIdentifier, string $assetToRemove): void
    {
        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $assetRepository->deleteByAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($assetFamilyIdentifier),
            AssetCode::fromString($assetToRemove)
        );
    }

    private function IsAssetHavingValue(string $assetFrom, string $assetTo): bool
    {
        /** @var Connection $sqlConnection */
        $sqlConnection = $this->get('database_connection');
        $statement = $sqlConnection->executeQuery(
            'SELECT value_collection FROM akeneo_asset_manager_asset WHERE identifier = :identifier',
            [
                'identifier' => $assetFrom,
            ]
        );
        $result = $statement->fetch(\PDO::FETCH_COLUMN);
        $values = json_decode($result, true);

        if (!isset($values[$this->currentAttributeIdentifier->normalize()])) {
            return false;
        }

        $data = $values[$this->currentAttributeIdentifier->normalize()]['data'];
        if (is_array($data)) {
            return in_array($assetTo, $data);
        }

        return $data === $assetTo;
    }
}
