<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\ProductAssetBundle\tests\EndToEnd\Command;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use PimEnterprise\Component\ProductAsset\Model\Asset;
use PimEnterprise\Component\ProductAsset\Model\Reference;
use PimEnterprise\Component\ProductAsset\Model\Variation;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GenerateMissingVariationFilesCommandIntegration extends TestCase
{
    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';
    private const ASSET_FIXTURES = [
        'shoe' => [
            'reference' => 'shoe.jpg',
            'variations' => ['shoe_variation.jpg']
        ],
        'mugs' => [
            'reference' => 'mugs.jpg',
            'variations' => ['mug_variation.jpg']
        ]
    ];

    /**
     * @test
     * @throws
     */
    public function it_generates_the_variations_for_one_asset()
    {
        $this->createStructure(['ecommerce' => ['en_US']]);
        $this->createAssetWithoutVariations('shoe');
        $this->assertVariationsFor('shoe', 0);

        $this->executeGenerateMissingVariationForAsset('shoe');

        $this->assertVariationsFor('shoe', 1);
    }

    /**
     * @test
     * @throws
     */
    public function it_generates_the_variations_for_all_assets()
    {
        $this->createStructure(['ecommerce' => ['en_US']]);
        $this->createAssetWithoutVariations('shoe');
        $this->createAssetWithoutVariations('mugs');
        $this->assertVariationsFor('shoe', 0);
        $this->assertVariationsFor('mugs', 0);

        $this->executeGenerateMissingVariationForAssetAllAssets();

        $this->assertVariationsFor('shoe', 1);
        $this->assertVariationsFor('mugs', 1);
    }

    /**
     * @test
     * @throws
     */
    public function it_generates_the_variations_for_one_localizable_asset()
    {
        $this->createStructure(['ecommerce' => ['en_US', 'fr_FR']]);
        $this->createLocalizableAssetWithoutVariations('shoe', ['en_US', 'fr_FR']);
        $this->assertVariationsFor('shoe', 0);

        $this->executeGenerateMissingVariationForAsset('shoe');

        $this->assertVariationsFor('shoe', 2);
    }

    /**
     * @test
     * @throws
     */
    public function when_a_new_channel_is_activated_it_generates_the_missing_variations_files_for_this_asset()
    {
        $this->createStructure(['ecommerce' => ['en_US']]);
        $this->createAssetWithOneVariation('shoe', 'ecommerce');
        $this->assertVariationsFor('shoe', 1);

        $this->createChannel('mobile');
        $this->executeGenerateMissingVariationForAsset('shoe');

        $this->assertVariationsFor('shoe', 2);
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function createStructure(array $structure)
    {
        if (empty($structure)) {
            throw new \Exception("There was no structure provided.");
        }

        foreach ($structure as $channelCode => $localesForChannel) {
            $this->createChannel($channelCode);
            $this->setActivatedLocalesForChannel($channelCode, $localesForChannel);
        }
    }

    private function createAssetWithoutVariations(string $assetCode): void
    {
        $referenceImage = $this->uploadAsset(self::ASSET_FIXTURES[$assetCode]['reference']);

        $asset = new Asset();
        $asset->setCode($assetCode);

        $reference = new Reference();
        $reference->setFileInfo($referenceImage);
        $reference->setAsset($asset);
        $this->get('pimee_product_asset.saver.reference')->save($reference);

        $asset->setReferences(new ArrayCollection([$reference]));
        $this->get('pimee_product_asset.saver.asset')->save($asset);
    }

    private function createAssetWithOneVariation(string $assetCode, string $channelCode): void
    {
        $variationImage = $this->uploadAsset(self::ASSET_FIXTURES[$assetCode]['variations'][0]);

        $asset = new Asset();
        $asset->setCode($assetCode);

        $referenceImage = $this->uploadAsset(self::ASSET_FIXTURES[$assetCode]['reference']);
        $reference = new Reference();
        $reference->setFileInfo($referenceImage);
        $reference->setAsset($asset);
        $this->get('pimee_product_asset.saver.reference')->save($reference);

        $variation = new Variation();
        $variation->setFileInfo($variationImage);
        $variation->setReference($reference);
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIDentifier($channelCode);
        $variation->setChannel($channel);
        $this->get('pimee_product_asset.saver.variation')->save($variation);

        $asset->setReferences(new ArrayCollection([$reference]));
        $this->get('pimee_product_asset.saver.asset')->save($asset);
    }

    /**
     * A localizable asset is an asset having more than one reference
     */
    private function createLocalizableAssetWithoutVariations(string $assetCode, array $locales)
    {
        $asset = new Asset();
        $asset->setCode($assetCode);

        $references = new ArrayCollection();
        foreach ($locales as $locale) {
            $referenceImage = $this->uploadAsset(self::ASSET_FIXTURES[$assetCode]['reference']);
            $reference = new Reference();
            $reference->setFileInfo($referenceImage);
            $reference->setAsset($asset);
            $reference->setLocale($this->get('pim_catalog.repository.locale')->findOneByIdentifier($locale));
            $this->get('pimee_product_asset.saver.reference')->save($reference);
            $references->add($reference);
        }

        $asset->setReferences($references);
        $this->get('pimee_product_asset.saver.asset')->save($asset);
    }

    private function createChannel(string $channelCode): void
    {
        if ($this->checkChannelExists($channelCode)) {
            return;
        }

        $categoryTree = $this->get('pim_catalog.repository.category')->findOneByIdentifier('master');
        $channel = new Channel();
        $channel->setCode($channelCode);
        $channel->setCategory($categoryTree);
        $this->get('pim_catalog.saver.channel')->save($channel);
    }

    private function checkChannelExists(string $channelCode): bool
    {
        return in_array(
            $channelCode,
            $this->get('pim_catalog.repository.channel')->getChannelCodes(),
            true
        );
    }

    private function setActivatedLocalesForChannel(string $channelCode, array $localesCodesForChannel) : void
    {
        $localeRepository = $this->get('pim_catalog.repository.locale');
        $localesForChannel = array_map(
            function (string $localeCode) use ($localeRepository) {
                return $localeRepository->findOneByIdentifier($localeCode);
            },
            $localesCodesForChannel
        );

        /** @var Channel $channel */
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        $channel->setLocales($localesForChannel);
        $this->get('pim_catalog.saver.channel')->save($channel);
    }

    private function assertVariationsFor(string $assetCode, int $variationsCount): void
    {
        /** @var Asset $asset */
        $asset = $this->get('pimee_product_asset.repository.asset')->findOneByIdentifier($assetCode);
        if (null === $asset) {
            throw new \Exception(sprintf('Asset "%s" does not exist', $assetCode));
        }

        $this->assertCount($variationsCount, $asset->getVariations());
    }

    private function executeGenerateMissingVariationForAsset(string $assetCode): void
    {
        $this->launchGenerateMissingVariationsCommand(['--asset' => $assetCode]);
    }

    private function executeGenerateMissingVariationForAssetAllAssets()
    {
        $this->launchGenerateMissingVariationsCommand([]);
    }

    /**
     * @param string $assetCode
     *
     * @return \SplFileInfo
     */
    private function uploadAsset(string $fileName): FileInfoInterface
    {
        $path = sprintf('/../../Common/images/%s', $fileName);
        $rawFile = new \SplFileInfo(__DIR__ . $path);

        $file = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store($rawFile, self::CATALOG_STORAGE_ALIAS);

        $this->get('akeneo_file_storage.saver.file')->save($file);

        return $file;
    }

    /**
     * @param string $assetCode
     *
     * @throws \Exception
     */
    private function launchGenerateMissingVariationsCommand(array $options): void
    {
        $application = new Application(static::$kernel);
        $application->setAutoExit(false);

        $commandName = 'pim:asset:generate-missing-variation';
        $arrayInput = [
            'command' => $commandName,
            '-v'      => true,
        ];
        $arrayInput = array_merge($arrayInput, $options);

        $command = $application->find($commandName);
        $commandTester = new CommandTester($command);
        $commandTester->execute($arrayInput);

        $output = $commandTester->getOutput();
        $exitCode = $commandTester->getStatusCode();
        if ($exitCode !== 0) {
            throw new \Exception(sprintf('The command "%s", was not executed successfully', $commandName));
        }
    }
}
