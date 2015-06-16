<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Builder\VariationBuilderInterface;
use PimEnterprise\Component\ProductAsset\Repository\AssetRepositoryInterface;
use PimEnterprise\Component\ProductAsset\VariationFileGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate the variation files of an asset depending on a channel and eventually a locale.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class GenerateVariationFileCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:asset:generate-variation');
        $this->setDescription('Generate the variation file for a given asset, channel and locale.');
        $this->addArgument('asset', InputArgument::REQUIRED);
        $this->addArgument('channel', InputArgument::REQUIRED);
        $this->addArgument('locale', InputArgument::OPTIONAL);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $assetCode = $input->getArgument('asset');
        if (null === $asset = $this->getAssetRepository()->findOneByIdentifier($assetCode)) {
            $output->writeln(sprintf('<error>The asset "%s" does not exist.</error>', $assetCode));

            return 1;
        }

        $channelCode = $input->getArgument('channel');
        if (null === $channel = $this->getChannelRepository()->findOneByIdentifier($channelCode)) {
            $output->writeln(sprintf('<error>The channel "%s" does not exist.</error>', $channelCode));

            return 1;
        }

        $locale = null;
        if (null !== $localeCode = $input->getArgument('locale')) {
            if (null === $locale = $this->getLocaleRepository()->findOneByIdentifier($localeCode)) {
                $output->writeln(sprintf('<error>The locale "%s" does not exist.</error>', $localeCode));

                return 1;
            }
        }

        $generator = $this->getVariationFileGenerator();

        try {
            $generator->generateFromAsset($asset, $channel, $locale);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        return 0;
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
