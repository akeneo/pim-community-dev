<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Asset\Bundle\Command;

use Akeneo\Asset\Component\Builder\ReferenceBuilderInterface;
use Akeneo\Asset\Component\Builder\VariationBuilderInterface;
use Akeneo\Asset\Component\Finder\AssetFinderInterface;
use Akeneo\Asset\Component\Persistence\Query\Sql\FindAssetCodesWithMissingVariationWithFileInterface;
use Akeneo\Asset\Component\Repository\AssetRepositoryInterface;
use Akeneo\Asset\Component\Repository\VariationRepositoryInterface;
use Akeneo\Asset\Component\VariationFileGeneratorInterface;
use Akeneo\Asset\Component\VariationsCollectionFilesGeneratorInterface;
use Akeneo\Channel\Component\Repository\ChannelRepositoryInterface;
use Akeneo\Channel\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Generate the variation files of an asset depending on a channel and eventually a locale.
 *
 * @author Julien Janvier <jjanvier@akeneo.com>
 */
class GenerateVariationFileCommand extends AbstractGenerationVariationFileCommand
{
    protected static $defaultName = 'pim:asset:generate-variation-file';

    /** @var VariationsCollectionFilesGeneratorInterface */
    private $variationsCollectionFilesGenerator;

    public function __construct(
        AssetFinderInterface $assetFinder,
        ReferenceBuilderInterface $referenceBuilder,
        VariationBuilderInterface $variationBuilder,
        SaverInterface $assetSaver,
        VariationFileGeneratorInterface $variationFileGenerator,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        AssetRepositoryInterface $assetRepository,
        VariationsCollectionFilesGeneratorInterface $variationsCollectionFilesGenerator
    ) {
        parent::__construct(
            $assetFinder,
            $referenceBuilder,
            $variationBuilder,
            $assetSaver,
            $variationFileGenerator,
            $channelRepository,
            $localeRepository,
            $assetRepository
        );

        $this->variationsCollectionFilesGenerator = $variationsCollectionFilesGenerator;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
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
            $asset = $this->retrieveAsset($input->getArgument('asset'));
            $channel = $this->retrieveChannel($input->getArgument('channel'));

            $locale = null;
            if (null !== $localeCode = $input->getArgument('locale')) {
                $locale = $this->retrieveLocale($localeCode);
            }

            $this->buildAsset($asset);
            $this->getAssetSaver()->save($asset);
            $reference = $this->retrieveReference($asset, $locale);
            $variation = $this->retrieveVariation($reference, $channel);
        } catch (\LogicException $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }

        $output->writeln($this->getGenerationMessage($asset, $channel, $locale));

        try {
            $this->variationsCollectionFilesGenerator->generate([$variation]);
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
