<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\VariationFileGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

/**
 * Generate the variation files of a reference.
 * @author Julien Janvier <jjanvier@akeneo.com>
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
        $msg = sprintf('Generation variation file for asset "%s"', $asset->getCode());

        if (null !== $locale) {
            $msg .= sprintf(', channel "%s" and locale "%s"...', $channel->getCode(), $locale->getCode());
        } else {
            $msg .= sprintf(' and channel "%s"...', $channel->getCode());
        }

        return $msg;
    }

    /**
     * @param AssetInterface   $asset
     * @param ChannelInterface $channel
     * @param LocaleInterface  $locale
     *
     * @return string
     */
    protected function getSkippingMessage(
        AssetInterface $asset,
        ChannelInterface $channel,
        LocaleInterface $locale = null
    ) {
        $msg = sprintf('Variation for asset "%s"', $asset->getCode());

        if (null !== $locale) {
            $msg .= sprintf(', channel "%s" and locale "%s"', $channel->getCode(), $locale->getCode());
        } else {
            $msg .= sprintf(' and channel "%s"', $channel->getCode());
        }

        $msg .= ' is locked. Skipping...';

        return $msg;
    }



    /**
     * @param $assetCode
     *
     * @return AssetInterface
     * @throws \LogicException
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
     * @return LocaleInterface
     * @throws \LogicException
     */
    protected function retrieveLocale($localeCode)
    {
        if (null === $locale = $this->getLocaleRepository()->findOneByIdentifier($localeCode)) {
            throw new \LogicException(sprintf('The locale "%s" does not exist.', $localeCode));
        }

        return $locale;
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
     * @return VariationBuilderInterfacee
     */
    protected function getVariationBuilder()
    {
        return $this->getContainer()->get('pimee_product_asset.builder.variation');
    }
}
