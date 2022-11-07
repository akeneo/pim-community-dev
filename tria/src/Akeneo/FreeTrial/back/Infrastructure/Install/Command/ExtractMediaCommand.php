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
use Akeneo\Tool\Component\FileStorage\FilesystemProvider;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command run in approximately 40 seconds with a concurrency of 20 HTTP calls to download medias.
 *
 * See \Akeneo\FreeTrial\Infrastructure\Install\Command\ExtractCatalogCommand for details.
 */
final class ExtractMediaCommand extends Command
{
    use InstallCatalogTrait;

    private FilesystemOperator $catalogFileSystem;

    private string $apiBaseUrl;
    private string $apiClientId;
    private string $apiSecret;
    private string $apiUsername;
    private string $apiPassword;

    public function __construct(
        FilesystemProvider $fileSystemManager,
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
            ->setName('akeneo:free-trial:extract-media')
            ->setDescription('Extract catalog data from an external PIM to build the Free-Trial catalog.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureAuthenticationParametersAreDefined();

        $apiClient = $this->buildApiClient($input);

        $extractMediaFiles = new ExtractMediaFiles($this->catalogFileSystem, $apiClient, $this->apiBaseUrl, $output);
        $extractMediaFiles();

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
