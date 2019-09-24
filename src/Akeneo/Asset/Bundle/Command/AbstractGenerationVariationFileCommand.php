<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Command;

use Akeneo\Asset\Component\Builder\ReferenceBuilderInterface;
use Akeneo\Asset\Component\Builder\VariationBuilderInterface;
use Akeneo\Asset\Component\Finder\AssetFinderInterface;
use Akeneo\Asset\Component\Model\AssetInterface;
use Akeneo\Asset\Component\Model\ReferenceInterface;
use Akeneo\Asset\Component\Model\VariationInterface;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Asset\Component\VariationFileGeneratorInterface;
use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Generate the variation files of a reference.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
abstract class AbstractGenerationVariationFileCommand extends Command
{
    /**
     * @var AssetFinderInterface
     */
    private $assetFinder;
    /**
     * @var ReferenceBuilderInterface
     */
    private $referenceBuilder;
    /**
     * @var VariationBuilderInterface
     */
    private $variationBuilder;
    /**
     * @var SaverInterface
     */
    private $assetSaver;
    /**
     * @var VariationFileGeneratorInterface
     */
    private $variationFileGenerator;
    /**
     * @var ChannelRepositoryInterface
     */
    private $channelRepository;
    /**
     * @var LocaleRepositoryInterface
     */
    private $localeRepository;
    /**
     * @var AssetRepositoryInterface
     */
    private $assetRepository;

    public function __construct(
        AssetFinderInterface $assetFinder,
        ReferenceBuilderInterface $referenceBuilder,
        VariationBuilderInterface $variationBuilder,
        SaverInterface $assetSaver,
        VariationFileGeneratorInterface $variationFileGenerator,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        AssetRepositoryInterface $assetRepository
    ) {
        parent::__construct();

        $this->assetFinder = $assetFinder;
        $this->referenceBuilder = $referenceBuilder;
        $this->variationBuilder = $variationBuilder;
        $this->assetSaver = $assetSaver;
        $this->variationFileGenerator = $variationFileGenerator;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->assetRepository = $assetRepository;
    }

    /**
     * @param AssetInterface   $asset
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return string
     */
    protected function getGenerationMessage(
        AssetInterface $asset,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        $msg = sprintf('Variation for asset "%s"', $asset->getCode());

        if (null !== $locale) {
            $msg .= sprintf(', channel "%s" and locale "%s"...', $channel->getCode(), $locale->getCode());
        } else {
            $msg .= sprintf(' and channel "%s"...', $channel->getCode());
        }

        return $msg;
    }

    /**
     * @return AssetFinderInterface
     */
    protected function getAssetFinder()
    {
        return $this->assetFinder;
    }

    /**
     * @param string $assetCode
     *
     * @throws \LogicException
     *
     * @return AssetInterface
     */
    protected function retrieveAsset($assetCode)
    {
        if (null === $asset = $this->getAssetRepository()->findOneByIdentifier($assetCode)) {
            throw new \LogicException(sprintf('The asset "%s" does not exist.', $assetCode));
        }

        return $asset;
    }

    /**
     * @param $localeCode
     *
     * @throws \LogicException
     *
     * @return LocaleInterface
     */
    protected function retrieveLocale($localeCode)
    {
        if (null === $locale = $this->getLocaleRepository()->findOneByIdentifier($localeCode)) {
            throw new \LogicException(sprintf('The locale "%s" does not exist.', $localeCode));
        }

        return $locale;
    }

    /**
     * @param AssetInterface $asset
     */
    protected function buildAsset(AssetInterface $asset)
    {
        $this->getReferenceBuilder()->buildMissingLocalized($asset);
        foreach ($asset->getReferences() as $reference) {
            $this->getVariationBuilder()->buildMissing($reference);
        }
    }

    /**
     * @return ReferenceBuilderInterface
     */
    protected function getReferenceBuilder()
    {
        return $this->referenceBuilder;
    }

    /**
     * @return VariationBuilderInterface
     */
    protected function getVariationBuilder()
    {
        return $this->variationBuilder;
    }

    /**
     * @return AssetSaver
     */
    protected function getAssetSaver()
    {
        return $this->assetSaver;
    }

    /**
     * @param AssetInterface  $asset
     * @param LocaleInterface $locale
     *
     * @throws \LogicException
     *
     * @return ReferenceInterface
     */
    protected function retrieveReference(AssetInterface $asset, LocaleInterface $locale = null)
    {
        return $this->getAssetFinder()->retrieveReference($asset, $locale);
    }

    /**
     * @param ReferenceInterface $reference
     * @param ChannelInterface   $channel
     *
     * @throws \LogicException
     *
     * @return VariationInterface
     */
    protected function retrieveVariation(ReferenceInterface $reference, ChannelInterface $channel)
    {
        return $this->getAssetFinder()->retrieveVariation($reference, $channel);
    }

    /**
     * @return VariationFileGeneratorInterface
     */
    protected function getVariationFileGenerator()
    {
        return $this->variationFileGenerator;
    }

    /**
     * @return ChannelRepositoryInterface
     */
    protected function getChannelRepository()
    {
        return $this->channelRepository;
    }

    /**
     * @return LocaleRepositoryInterface
     */
    protected function getLocaleRepository()
    {
        return $this->localeRepository;
    }

    /**
     * @return AssetRepositoryInterface
     */
    protected function getAssetRepository()
    {
        return $this->assetRepository;
    }
}
