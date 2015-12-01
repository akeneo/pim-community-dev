<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Builder\VariationBuilderInterface;
use PimEnterprise\Component\ProductAsset\Finder\AssetFinderInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\VariationFileGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Generate the variation files of a reference.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
abstract class AbstractGenerationVariationFileCommand extends ContainerAwareCommand
{
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
        return $this->getContainer()->get('pimee_product_asset.finder.asset');
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
        return $this->getContainer()->get('pimee_product_asset.variation_file_generator');
    }

    /**
     * @return ChannelRepositoryInterface
     */
    protected function getChannelRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.channel');
    }

    /**
     * @return LocaleRepositoryInterface
     */
    protected function getLocaleRepository()
    {
        return $this->getContainer()->get('pim_catalog.repository.locale');
    }

    /**
     * @return AssetRepositoryInterface
     */
    protected function getAssetRepository()
    {
        return $this->getContainer()->get('pimee_product_asset.repository.asset');
    }

    /**
     * @return VariationBuilderInterface
     */
    protected function getVariationBuilder()
    {
        return $this->getContainer()->get('pimee_product_asset.builder.variation');
    }
}
