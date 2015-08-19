<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Command;

use Pim\Bundle\CatalogBundle\Model\ChannelInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate the variation files of an asset depending on a channel and eventually a locale.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class GenerateVariationFileCommand extends AbstractGenerationVariationFileCommand
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
        try {
            $asset   = $this->retrieveAsset($input->getArgument('asset'));
            $channel = $this->retrieveChannel($input->getArgument('channel'));

            $locale = null;
            if (null !== $localeCode = $input->getArgument('locale')) {
                $this->retrieveLocale($localeCode);
            }

            $reference = $this->retrieveReference($asset, $locale);
            $variation = $this->retrieveVariation($reference, $channel);
        } catch (\LogicException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        $generator = $this->getVariationFileGenerator();
        $output->writeln($this->getGenerationMessage($asset, $channel, $locale));

        try {
            $generator->generate($variation);
        } catch (\Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        $output->writeln('<info>Done!</info>');

        return 0;
    }

    /**
     * @param $channelCode
     *
     * @throws \LogicException
     *
     * @return ChannelInterface
     */
    protected function retrieveChannel($channelCode)
    {
        if (null === $channel = $this->getChannelRepository()->findOneByIdentifier($channelCode)) {
            throw new \LogicException(sprintf('The channel "%s" does not exist.', $channelCode));
        }

        return $channel;
    }
}
