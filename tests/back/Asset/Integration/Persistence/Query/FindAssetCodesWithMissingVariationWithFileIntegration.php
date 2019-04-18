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

namespace AkeneoTestEnterprise\Asset\Integration\Persistence\Query;

use Akeneo\Asset\Component\Model\Asset;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\Reference;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Model\Variation;
use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Doctrine\Common\Collections\ArrayCollection;

class FindAssetCodesWithMissingVariationWithFileIntegration extends TestCase
{
    private const IMAGES_PATH = '/../../../Common/images';
    private const REFERENCE_IMAGE_NAME = 'shoe.jpg';
    private const VARIATION_IMAGE_NAME = 'shoe_variation.jpg';

    private const CATALOG_STORAGE_ALIAS = 'catalogStorage';

    private const NOT_LOCALIZED_ASSET_WITHOUT_MISSING_VARIATION_FILE = 'asset_1';
    private const NOT_LOCALIZED_ASSET_WITHOUT_FILES = 'asset_2';
    private const NOT_LOCALIZED_ASSET_WITH_MISSING_VARIATION = 'asset_3';

    private const LOCALIZED_ASSET_WITHOUT_MISSING_VARIATION_FILE = 'asset_4';
    private const LOCALIZED_ASSET_WITHOUT_FILES = 'asset_5';
    private const LOCALIZED_ASSET_WITH_MISSING_VARIATION = 'asset_6';

    /** @var ChannelInterface */
    private $channelWithOneLocale;

    /** @var ChannelInterface */
    private $channelWithTwoLocales;

    /** @var LocaleInterface */
    private $localeFr;

    /** @var LocaleInterface */
    private $localeUs;

    /** @var LocaleInterface */
    private $localeDe;

    /**
     * @test
     */
    public function it_finds_the_codes_of_the_assets_with_missing_generated_variation_file()
    {
        $this->loadLocales();
        $this->loadChannels();

        $this->loadNotLocalizedAssetWithoutMissingVariationFile();
        $this->loadNotLocalizedAssetWithoutFile();
        $this->loadNotLocalizedAssetWithMissingVariation();
        $this->loadLocalizedAssetWithoutMissingVariationFile();
        $this->loadLocalizedAssetWithoutFiles();
        $this->loadLocalizedAssetWithMissingVariation();

        $expectedAssets = [
            self::NOT_LOCALIZED_ASSET_WITH_MISSING_VARIATION,
            self::LOCALIZED_ASSET_WITH_MISSING_VARIATION,
        ];

        $assets = $this->get('pimee_product_asset.query.find_asset_codes_with_missing_variation_with_file')->execute();
        sort($assets);

        $this->assertEquals($expectedAssets, $assets);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadLocales(): void
    {
        $localeRepository = $this->get('pim_catalog.repository.locale');
        $this->localeFr = $localeRepository->findOneByIdentifier('fr_FR');
        $this->localeUs = $localeRepository->findOneByIdentifier('en_US');
        $this->localeDe = $localeRepository->findOneByIdentifier('de_DE');
    }

    private function loadChannels(): void
    {
        // With the minimal catalog, there's already the channel 'ecommerce' with the locale 'en_US'
        $this->channelWithOneLocale = $this->get('pim_catalog.repository.channel')->findOneByIDentifier('ecommerce');

        $this->channelWithTwoLocales = new Channel();
        $this->channelWithTwoLocales->setCode('channel_with_two_locales');
        $this->channelWithTwoLocales->setCategory($this->channelWithOneLocale->getCategory());
        $this->channelWithTwoLocales->setLocales([$this->localeFr, $this->localeUs]);

        $this->get('pim_catalog.saver.channel')->save($this->channelWithTwoLocales);
    }

    private function loadNotLocalizedAssetWithoutMissingVariationFile(): void
    {
        $asset = new Asset();
        $asset->setCode(self::NOT_LOCALIZED_ASSET_WITHOUT_MISSING_VARIATION_FILE);

        $referenceFile = $this->uploadAssetFile(self::REFERENCE_IMAGE_NAME);

        $reference = $this->loadReference($asset, null, $referenceFile);

        $this->loadVariation($reference, $this->channelWithOneLocale);
        $this->loadVariation($reference, $this->channelWithTwoLocales);

        $asset->setReferences(new ArrayCollection([$reference]));
        $this->get('pimee_product_asset.saver.asset')->save($asset);
    }

    private function loadNotLocalizedAssetWithoutFile(): void
    {
        $asset = new Asset();
        $asset->setCode(self::NOT_LOCALIZED_ASSET_WITHOUT_FILES);

        $reference = $this->loadReference($asset, null, null);

        $asset->setReferences(new ArrayCollection([$reference]));
        $this->get('pimee_product_asset.saver.asset')->save($asset);
    }

    private function loadNotLocalizedAssetWithMissingVariation(): void
    {
        $asset = new Asset();
        $asset->setCode(self::NOT_LOCALIZED_ASSET_WITH_MISSING_VARIATION);

        $referenceFile = $this->uploadAssetFile(self::REFERENCE_IMAGE_NAME);

        $reference = $this->loadReference($asset, null, $referenceFile);

        $this->loadVariation($reference, $this->channelWithOneLocale);

        $asset->setReferences(new ArrayCollection([$reference]));
        $this->get('pimee_product_asset.saver.asset')->save($asset);
    }

    private function loadLocalizedAssetWithoutMissingVariationFile(): void
    {
        $asset = new Asset();
        $asset->setCode(self::LOCALIZED_ASSET_WITHOUT_MISSING_VARIATION_FILE);

        $referenceFile1 = $this->uploadAssetFile(self::REFERENCE_IMAGE_NAME);
        $referenceFile2 = $this->uploadAssetFile(self::REFERENCE_IMAGE_NAME);

        $referenceWithFile = $this->loadReference($asset, $this->localeFr, $referenceFile1);
        $referenceWithoutFile = $this->loadReference($asset, $this->localeUs, null);
        $referenceForNotActivatedLocale = $this->loadReference($asset, $this->localeDe, $referenceFile2);

        $this->loadVariation($referenceWithFile, $this->channelWithTwoLocales);

        $asset->setReferences(new ArrayCollection([
            $referenceWithFile,
            $referenceWithoutFile,
            $referenceForNotActivatedLocale
        ]));
        $this->get('pimee_product_asset.saver.asset')->save($asset);
    }

    private function loadLocalizedAssetWithoutFiles(): void
    {
        $asset = new Asset();
        $asset->setCode(self::LOCALIZED_ASSET_WITHOUT_FILES);

        $referenceUs = $this->loadReference($asset, $this->localeUs, null);
        $referenceFr = $this->loadReference($asset, $this->localeFr, null);

        $asset->setReferences(new ArrayCollection([$referenceUs, $referenceFr]));
        $this->get('pimee_product_asset.saver.asset')->save($asset);
    }

    private function loadLocalizedAssetWithMissingVariation(): void
    {
        $asset = new Asset();
        $asset->setCode(self::LOCALIZED_ASSET_WITH_MISSING_VARIATION);

        $referenceFile1 = $this->uploadAssetFile(self::REFERENCE_IMAGE_NAME);
        $referenceFile2 = $this->uploadAssetFile(self::REFERENCE_IMAGE_NAME);

        $referenceUs = $this->loadReference($asset, $this->localeUs, $referenceFile1);
        $referenceFr = $this->loadReference($asset, $this->localeFr, $referenceFile2);

        $asset->setReferences(new ArrayCollection([$referenceUs, $referenceFr]));
        $this->get('pimee_product_asset.saver.asset')->save($asset);
    }

    private function loadReference(AssetInterface $asset, ?LocaleInterface $locale, ?FileInfoInterface $file): ReferenceInterface
    {
        $reference = new Reference();
        $reference->setAsset($asset);

        if (null !== $file) {
            $reference->setFileInfo($file);
        }
        if (null !== $locale) {
            $reference->setLocale($locale);
        }

        $this->get('pimee_product_asset.saver.reference')->save($reference);

        return $reference;
    }

    private function loadVariation(ReferenceInterface $reference, ChannelInterface $channel): void
    {
        $fileInfo = $this->uploadAssetFile(self::VARIATION_IMAGE_NAME);

        $variation = new Variation();
        $variation->setReference($reference);
        $variation->setChannel($channel);
        $variation->setFileInfo($fileInfo);

        $this->get('pimee_product_asset.saver.variation')->save($variation);
    }

    private function uploadAssetFile(string $fileName): FileInfoInterface
    {
        $filesPath = realpath(__DIR__ . self::IMAGES_PATH);
        $fileInfo = new \SplFileInfo(sprintf('%s/%s', $filesPath, $fileName));

        $file = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store($fileInfo, self::CATALOG_STORAGE_ALIAS);
        $this->get('akeneo_file_storage.saver.file')->save($file);

        return $file;
    }
}
