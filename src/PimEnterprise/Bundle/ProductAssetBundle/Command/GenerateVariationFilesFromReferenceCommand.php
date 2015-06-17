<?php

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\ReferenceInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate the variation files of a reference.
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class GenerateVariationFilesFromReferenceCommand extends AbstractGenerationVariationFileCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('pim:asset:generate-variations-from-reference');
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
                $this->retrieveLocale($localeCode);
            }

            $reference = $this->retrieveReference($asset, $locale);
        } catch (\LogicException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        $generator = $this->getVariationFileGenerator();

        foreach ($reference->getVariations() as $variation) {
            if (!$variation->isLocked()) {
                try {
                    $output->writeln($this->getGenerationMessage($asset, $variation->getChannel(), $locale));
                    $generator->generateFromAsset($asset, $variation->getChannel(), $locale);
                } catch (\Exception $e) {
                    $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

                    return 1;
                }
            } else {
                $output->writeln($this->getSkippingMessage($asset, $variation->getChannel(), $locale));
            }
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }

    /**
     * @param AssetInterface  $asset
     * @param LocaleInterface $locale
     *
     * @return ReferenceInterface
     * @throws \LogicException
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
}
