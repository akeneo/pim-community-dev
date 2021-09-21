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
use Akeneo\FreeTrial\Infrastructure\Install\Command\Extractor\ExtractStructure;
use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\Enrichment\Component\FileStorage;
use League\Flysystem\FilesystemInterface;
use League\Flysystem\MountManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ExtractCatalogCommand extends Command
{
    use InstallCatalogTrait;

    private FilesystemInterface $catalogFileSystem;

    private array $mediaFileAttributes = [];
    private array $downloadedMediaFiles = [];
    private array $productModelsAttributes = [];

    private string $apiBaseUrl;
    private string $apiClientId;
    private string $apiSecret;
    private string $apiUsername;
    private string $apiPassword;

    public function __construct(
        MountManager $fileSystemManager,
        string $apiBaseUrl,
        string $apiClientId,
        string $apiSecret,
        string $apiUsername,
        string $apiPassword
    ) {
        parent::__construct();

        $this->catalogFileSystem = $fileSystemManager->getFilesystem(FileStorage::CATALOG_STORAGE_ALIAS);
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiClientId = $apiClientId;
        $this->apiSecret = $apiSecret;
        $this->apiUsername = $apiUsername;
        $this->apiPassword = $apiPassword;
    }

    protected function configure()
    {
        $this
            ->setName('akeneo:free-trial:extract-catalog')
            ->setDescription('Extract catalog data from an external PIM to build the Free-Trial catalog.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureAuthenticationParametersAreDefined();

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Extract catalog data from %s', $this->apiBaseUrl));

        $apiClient = $this->buildApiClient($input);

        $extractMediaFiles = new ExtractMediaFiles($this->catalogFileSystem, $apiClient, $io);
        $extractMediaFiles();

        $extractProducts = new ExtractProducts($apiClient, $io);
        $extractProducts();

        $extractStructure = new ExtractStructure($apiClient, $io);
        $extractStructure();

        $io->success('Catalog extracted!');

        return 0;
    }

    private function buildApiClient(InputInterface $input): AkeneoPimClientInterface
    {
        $clientBuilder = new AkeneoPimClientBuilder($this->apiBaseUrl);

        return $clientBuilder->buildAuthenticatedByPassword(
            $this->apiClientId,
            $this->apiSecret,
            $this->apiUsername,
            $this->apiPassword
        );
    }

    private function ensureAuthenticationParametersAreDefined(): void
    {
        if ('' === $this->apiBaseUrl) {
            throw new \Exception('The API base URI is not defined (env var FT_CATALOG_API_BASE_URI');
        }
        if ('' === $this->apiClientId) {
            throw new \Exception('The API client id is not defined (env var FT_CATALOG_API_CLIENT_ID');
        }
        if ('' === $this->apiSecret) {
            throw new \Exception('The API secret is not defined (env var FT_CATALOG_API_SECRET');
        }
        if ('' === $this->apiUsername) {
            throw new \Exception('The API username is not defined (env var FT_CATALOG_API_USERNAME');
        }
        if ('' === $this->apiPassword) {
            throw new \Exception('The API password is not defined (env var FT_CATALOG_API_PASSWORD');
        }
    }
}
