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

use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use PimEnterprise\Component\ProductAsset\ProcessedItem;
use PimEnterprise\Component\ProductAsset\VariationsCollectionFilesGeneratorInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate the variation files of a reference.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class GenerateVariationFilesFromReferenceCommand extends AbstractGenerationVariationFileCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:asset:generate-variation-files-from-reference');
        $this->setDescription('Generate the variation files of a reference.');
        $this->addArgument('asset', InputArgument::REQUIRED);
        $this->addArgument('locale', InputArgument::OPTIONAL);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $asset = $this->retrieveAsset($input->getArgument('asset'));

            $locale = null;
            if (null !== $localeCode = $input->getArgument('locale')) {
                $locale = $this->retrieveLocale($localeCode);
            }

            $reference = $this->retrieveReference($asset, $locale);
        } catch (\LogicException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        $processedList = $this
            ->getVariationsCollectionFileGenerator()
            ->generate($reference->getVariations()->toArray());

        foreach ($processedList as $item) {
            $msg = $this->getGenerationMessage($asset, $item->getItem()->getChannel(), $reference->getLocale());

            switch ($item->getState()) {
                case ProcessedItem::STATE_ERROR:
                    $msg = sprintf('<error>%s\n%s</error>', $msg, $item->getReason());
                    break;
                case ProcessedItem::STATE_SKIPPED:
                    $msg = sprintf('%s <comment>Skipped (%s)</comment>', $msg, $item->getReason());
                    break;
                default:
                    $msg = sprintf('%s <info>Done!</info>', $msg);
                    break;
            }

            $output->writeln($msg);
        }

        $output->writeln('<info>Done!</info>');

        return 0;
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
     * @return VariationsCollectionFilesGeneratorInterface
     */
    protected function getVariationsCollectionFileGenerator()
    {
        return $this->getContainer()->get('pimee_product_asset.variations_collection_files_generator');
    }
}
