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
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\Model\VariationInterface;
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
        $msg = sprintf('Variation for asset "%s"', $asset->getCode());

        if (null !== $locale) {
            $msg .= sprintf(', channel "%s" and locale "%s"...', $channel->getCode(), $locale->getCode());
        } else {
            $msg .= sprintf(' and channel "%s"...', $channel->getCode());
        }

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
     * @param AssetInterface  $asset
     * @param LocaleInterface $locale
     *
     * @throws \LogicException
     *
     * @return ReferenceInterface
     */
    protected function retrieveReference(AssetInterface $asset, LocaleInterface $locale = null)
    {
        if (null === $reference = $asset->getReference($locale)) {
            if (null === $locale) {
                $msg = sprintf('The asset "%s" has no reference without locale.', $asset->getCode());
            } else {
                $msg = sprintf(
                    'The asset "%s" has no reference for the locale "%s".',
                    $asset->getCode(),
                    $locale->getCode()
                );
            }

            throw new \LogicException($msg);
        }

        return $reference;
    }

    /**
     * @param ReferenceInterface $reference
     * @param ChannelInterface   $channel
     *
     * @return VariationInterface
     *
     * @throws \LogicException
     */
    protected function retrieveVariation(ReferenceInterface $reference, ChannelInterface $channel)
    {
        if (null === $variation = $reference->getVariation($channel)) {
            throw new \LogicException(
                sprintf(
                    'The reference "%s" has no variation for the channel "%s".',
                    $reference->getId(),
                    $channel->getCode()
                )
            );
        }

        return $variation;
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
