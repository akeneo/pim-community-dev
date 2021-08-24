<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Command;

use Akeneo\FreeTrial\Infrastructure\Install\Command\Extractor\ExtractMediaFiles;
use Akeneo\FreeTrial\Infrastructure\Install\Command\Extractor\ExtractProducts;
use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ExtractCatalogCommand extends Command
{
    use InstallCatalogTrait;

    private array $mediaFileAttributes = [];
    private array $downloadedMediaFiles = [];
    private array $productModelsAttributes = [];

    protected function configure()
    {
        $this
            ->setName('akeneo:free-trial:extract-catalog')
            ->setDescription('Extract catalog data from an external PIM to build the Free-Trial catalog.')
            ->addArgument('api-url', InputArgument::REQUIRED, 'API URL')
            ->addArgument('api-client-id', InputArgument::REQUIRED, 'API client id')
            ->addArgument('api-secret', InputArgument::REQUIRED, 'API secret')
            ->addArgument('api-username', InputArgument::REQUIRED, 'API username')
            ->addArgument('api-password', InputArgument::REQUIRED, 'API password')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Extract catalog data from %s', $input->getArgument('api-url')));

        $apiClient = $this->buildApiClient($input);

        $extractMediaFiles = new ExtractMediaFiles($apiClient, $io);
        $extractMediaFiles();

        $extractProducts = new ExtractProducts($apiClient, $io);
        $extractProducts();

        $io->success('Catalog extracted!');

        return 0;
    }

    private function buildApiClient(InputInterface $input): AkeneoPimClientInterface
    {
        $clientBuilder = new AkeneoPimClientBuilder($input->getArgument('api-url'));

        return $clientBuilder->buildAuthenticatedByPassword(
            $input->getArgument('api-client-id'),
            $input->getArgument('api-secret'),
            $input->getArgument('api-username'),
            $input->getArgument('api-password')
        );
    }
}
