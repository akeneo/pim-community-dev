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
        $this->setName('pim:asset:add-locale')->setDescription('Add the new channel\'s locales to the assets');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $qb = $this->getContainer()->get('pimee_product_asset.repository.asset')->createQueryBuilder('asset');
        $assets = new Cursor(
            $qb,
            $qb->getEntityManager(),
            10
        );

        foreach ($assets as $asset) {
            if ($asset->isLocalizable()) {
                $references = $asset->getReferences();
                $assetLocales = [];
                foreach ($references as $reference) {
                    if (null !== $reference->getLocale()) {
                        $assetLocales[] =  $reference->getLocale()->getCode();
                    }
                }

                $activatedLocales = $this->getContainer()->get('pim_catalog.repository.locale')->getActivatedLocales();
                foreach ($activatedLocales as $locale) {
                    if (!in_array($locale, $assetLocales)) {
                        $reference = $this->getContainer()->get('pimee_product_asset.factory.reference')
                            ->create($locale);
                        $reference->setAsset($asset);
                        $this->getContainer()->get('pimee_product_asset.saver.reference')->save($reference);
                        $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher')->detach($reference);
                    }
                }
            }

            $this->getContainer()->get('akeneo_storage_utils.doctrine.object_detacher')->detach($asset);
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }
}
