<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use PimEnterprise\Bundle\ProductAssetBundle\Command\Cursor\Cursor;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Adds new channel locales to localizable assets.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
class AddLocaleChannelToAssetsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('pim:asset:add-locale')
            ->setDescription('Add the new channel\'s locales to the assets');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $qb = $this->getContainer()
            ->get('pimee_product_asset.repository.asset')
            ->createQueryBuilder('asset');

        $assets = new Cursor($qb, $qb->getEntityManager(), 10);

        foreach ($assets as $asset) {
            if ($asset->isLocalizable()) {
                $this->createMissingVariations($asset);
                $this->createMissingReferences($asset);
            }

            $this->getContainer()
                ->get('akeneo_storage_utils.doctrine.object_detacher')
                ->detach($asset);
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }

    /**
     * @param AssetInterface $asset
     */
    protected function createMissingReferences(AssetInterface $asset)
    {
        $existingReferenceLocales = [];
        foreach ($asset->getReferences() as $reference) {
            $existingReferenceLocales[] = $reference->getLocale()->getCode();
        }

        $activatedLocales = $this->getContainer()
            ->get('pim_catalog.repository.locale')
            ->getActivatedLocales();

        foreach ($activatedLocales as $locale) {
            if (!in_array($locale, $existingReferenceLocales)) {
                $reference = $this->getContainer()
                    ->get('pimee_product_asset.factory.reference')
                    ->create($locale);
                $reference->setAsset($asset);

                $this->getContainer()
                    ->get('pimee_product_asset.saver.reference')
                    ->save($reference);
            }
        }
    }

    /**
     * @param AssetInterface $asset
     */
    protected function createMissingVariations(AssetInterface $asset)
    {
        $references = $asset->getReferences();
        foreach ($references as $reference) {
            $locale = $reference->getlocale();
            $existingVariationChannels = [];

            foreach ($reference->getVariations() as $variation) {
                $existingVariationChannels[] = $variation->getChannel()->getCode();
            }

            foreach ($locale->getChannels() as $channel) {
                if (!in_array($channel->getCode(), $existingVariationChannels)) {
                    $variation = $this->getContainer()
                        ->get('pimee_product_asset.factory.variation')
                        ->create($channel);
                    $variation->setReference($reference);

                    $this->getContainer()
                        ->get('pimee_product_asset.saver.reference')
                        ->save($reference);
                }
            }
        }
    }
}
