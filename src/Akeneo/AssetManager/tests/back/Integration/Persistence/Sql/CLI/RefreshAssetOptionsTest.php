<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Integration\Persistence\Sql\CLI;

use Akeneo\AssetManager\Domain\Model\Asset\Asset;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\Asset\AssetIdentifier;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ChannelReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\LocaleReference;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionCollectionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\OptionData;
use Akeneo\AssetManager\Domain\Model\Asset\Value\Value;
use Akeneo\AssetManager\Domain\Model\Asset\Value\ValueCollection;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamily;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\RuleTemplateCollection;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsReadOnly;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIsRequired;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\AttributeOption;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOrder;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerChannel;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeValuePerLocale;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\OptionCollectionAttribute;
use Akeneo\AssetManager\Domain\Model\Image;
use Akeneo\AssetManager\Domain\Model\LabelCollection;
use Akeneo\AssetManager\Domain\Repository\AssetFamilyRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AssetRepositoryInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;
use Akeneo\AssetManager\Integration\SqlIntegrationTestCase;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RefreshAssetOptionsTest extends SqlIntegrationTestCase
{
    private ?AssetFamilyIdentifier $currentAssetFamilyIdentifier = null;

    private ?AttributeIdentifier $currentAttributeIdentifier = null;

    private AssetIdentifier $currentAssetIdentifier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetDB();
    }

    /**
     * @test
     */
    public function it_refreshes_a_asset_having_an_option_that_has_been_removed(): void
    {
        $this->createOptionAttributeWithOptions(['red']);
        $this->createAssetHavingOption('red');
        $this->removeOptionFromAttribute('red');
        $this->assertTrue($this->IsAssetHavingValue('red'));

        $this->clearCache();
        $this->runRefreshAssetsCommand();

        $this->assertFalse($this->IsAssetHavingValue('red'));
    }

    /**
     * @test
     */
    public function it_refreshes_a_asset_having_one_of_its_option_removed(): void
    {
        $this->createOptionCollectionAttributeWithOptions(['red', 'blue']);
        $this->createAssetHavingOptions(['red', 'blue']);
        $this->removeOptionFromAttribute('red');
        $this->assertTrue($this->IsAssetHavingValue('red'));
        $this->assertTrue($this->IsAssetHavingValue('blue'));

        $this->clearCache();
        $this->runRefreshAssetsCommand();

        $this->assertTrue($this->IsAssetHavingValue('blue'));
        $this->assertFalse($this->IsAssetHavingValue('red'));
    }

    private function resetDB(): void
    {
        $this->get('akeneoasset_manager.tests.helper.database_helper')->resetDatabase();
    }

    private function runRefreshAssetsCommand(): void
    {
        $application = new Application(self::$kernel);
        $command = $application->find('akeneo:asset-manager:refresh-assets');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'command' => $command->getName(),
            '--all'   => true,
        ]);
    }

    /**
     * @param string[] $options
     */
    private function createOptionAttributeWithOptions(array $options)
    {
        $this->loadAssetFamily('designer');
        $this->loadOptionAttributeWithOptions($options);
    }

    /**
     * @param string[] $options
     */
    private function createOptionCollectionAttributeWithOptions(array $options)
    {
        $this->loadAssetFamily('designer');
        $this->loadOptionCollectionAttributeWithOptions($options);
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
                Image::createEmpty(),
                RuleTemplateCollection::empty()
            )
        );
        $this->currentAssetFamilyIdentifier = $assetFamilyIdentifier;
    }

    private function loadOptionAttributeWithOptions($optionCodes): void
    {
        $this->currentAttributeIdentifier = AttributeIdentifier::fromString('color');
        $optionAttribute = OptionAttribute::create(
            $this->currentAttributeIdentifier,
            $this->currentAssetFamilyIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        foreach ($optionCodes as $optionCode) {
            $optionAttribute->addOption(AttributeOption::create(OptionCode::fromString($optionCode),
                LabelCollection::fromArray([])));
        }

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function loadOptionCollectionAttributeWithOptions($optionCodes): void
    {
        $this->currentAttributeIdentifier = AttributeIdentifier::fromString('color');
        $optionAttribute = OptionCollectionAttribute::create(
            $this->currentAttributeIdentifier,
            $this->currentAssetFamilyIdentifier,
            AttributeCode::fromString('color'),
            LabelCollection::fromArray([]),
            AttributeOrder::fromInteger(2),
            AttributeIsRequired::fromBoolean(true),
            AttributeIsReadOnly::fromBoolean(false),
            AttributeValuePerChannel::fromBoolean(false),
            AttributeValuePerLocale::fromBoolean(false)
        );
        foreach ($optionCodes as $optionCode) {
            $optionAttribute->addOption(AttributeOption::create(OptionCode::fromString($optionCode),
                LabelCollection::fromArray([])));
        }

        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        $attributeRepository->create($optionAttribute);
    }

    private function createAssetHavingOption(string $optionCode): void
    {
        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->currentAssetIdentifier = AssetIdentifier::fromString('a_asset');
        $assetRepository->create(
            Asset::create(
                $this->currentAssetIdentifier,
                $this->currentAssetFamilyIdentifier,
                AssetCode::fromString('a_asset'),
                ValueCollection::fromValues([
                    Value::create(
                        $this->currentAttributeIdentifier,
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        OptionData::createFromNormalize($optionCode)
                    ),
                ])
            )
        );
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();
    }

    /**
     * @param string[] $optionCodes
     */
    private function createAssetHavingOptions(array $optionCodes): void
    {
        /** @var AssetRepositoryInterface $assetRepository */
        $assetRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.asset');
        $this->currentAssetIdentifier = AssetIdentifier::fromString('a_asset');
        $optionCodes = array_map(
            fn(string $optionCode) => OptionCode::fromString($optionCode), $optionCodes
        );
        $assetRepository->create(
            Asset::create(
                $this->currentAssetIdentifier,
                $this->currentAssetFamilyIdentifier,
                AssetCode::fromString('a_asset'),
                ValueCollection::fromValues([
                    Value::create(
                        $this->currentAttributeIdentifier,
                        ChannelReference::noReference(),
                        LocaleReference::noReference(),
                        OptionCollectionData::fromOptionCodes($optionCodes)
                    ),
                ])
            )
        );
        $this->get('akeneo_assetmanager.client.asset')->refreshIndex();
    }

    private function removeOptionFromAttribute(string $optionToRemove): void
    {
        /** @var AttributeRepositoryInterface $attributeRepository */
        $attributeRepository = $this->get('akeneo_assetmanager.infrastructure.persistence.repository.attribute');
        /** @var OptionAttribute $optionAttribute */
        $optionAttribute = $attributeRepository->getByIdentifier($this->currentAttributeIdentifier);
        $optionsToKeep = array_filter(
            $optionAttribute->getAttributeOptions(),
            fn(AttributeOption $option) => $optionToRemove !== (string) $option->getCode()
        );
        $optionAttribute->setOptions($optionsToKeep);
        $attributeRepository->update($optionAttribute);
    }

    private function IsAssetHavingValue(string $optionCode): bool
    {
        /** @var Connection $sqlConnection */
        $sqlConnection = $this->get('database_connection');
        $statement = $sqlConnection->executeQuery(
            'SELECT value_collection FROM akeneo_asset_manager_asset WHERE identifier = :identifier',
            [
                'identifier' => $this->currentAssetIdentifier->normalize(),
            ]
        );
        $result = $statement->fetch(\PDO::FETCH_COLUMN);
        $values = json_decode($result, true);

        if (!isset($values[$this->currentAttributeIdentifier->normalize()])) {
            return false;
        }

        $data = $values[$this->currentAttributeIdentifier->normalize()]['data'];
        if (is_array($data)) {
            return in_array($optionCode, $data);
        }

        return $data === $optionCode;
    }

    private function clearCache(): void
    {
        $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_value_key_collection')->clearCache();
        $this->get('akeneo_assetmanager.infrastructure.persistence.query.find_attributes_indexed_by_identifier')->clearCache();
    }
}
