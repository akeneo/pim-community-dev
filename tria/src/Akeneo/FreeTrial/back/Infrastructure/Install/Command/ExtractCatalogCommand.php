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

use Symfony\Component\Process\Exception\LogicException;
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
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

/**
 * Extract the free trial catalog from a reference GE environment. In order to propose a free trial instance quickly, the performance
 * of this tract is critical and therefore it should run as fast as possible.
 * For this reason:
 * - media files are exported in a subprocess, and use concurrent call to the API to download the medias. It last approximately 45 seconds.
 * - product, product model and structure run sequentially. It last approximately 45 seconds (30 seconds just for attributes with attribute options).
 *
 * The running time of the command is therefore approximately 45 seconds. Without concurrent HTTP calls and no subprocess, the command would run in ~6 minutes.
 */
final class ExtractCatalogCommand extends Command
{
    use InstallCatalogTrait;
    private const TIMEOUT_EXTRACT_MEDIA_IN_SECONDS = 500;
    private const RUNNING_PROCESS_CHECK_INTERVAL_IN_MICROSECONDS = 200000;

    public function __construct(
        private string $apiBaseUrl,
        private string $apiClientId,
        private string $apiSecret,
        private string $apiUsername,
        private string $apiPassword,
        private string $projectDir
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('akeneo:free-trial:extract-catalog')
            ->setDescription('Extract catalog data from an external PIM to build the Free-Trial catalog.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensureAuthenticationParametersAreDefined();

        $output->writeln(sprintf('<info>Extract Free-Trial catalog data from %s</info>', $this->apiBaseUrl));

        $apiClient = $this->buildApiClient($input);

        $process = $this->startExtractMediaInSubProcess();

        $extractProducts = new ExtractProducts($apiClient, $output);
        $extractProducts();

        $extractStructure = new ExtractStructure($apiClient, $output);
        $extractStructure();

        $this->waitExtractMedia($process, $output);

        $output->writeln('<info>Free-Trial Catalog successfully extracted!</info>');

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

    private function startExtractMediaInSubProcess(): Process
    {
        $pathFinder = new PhpExecutableFinder();
        $command = [
            $pathFinder->find(),
            sprintf('%s/bin/console', $this->projectDir),
            'akeneo:free-trial:extract-media'
        ];
        $process = new Process(command: $command, timeout: self::TIMEOUT_EXTRACT_MEDIA_IN_SECONDS);
        $process->start();

        return $process;
    }

    /**
     * @param Process $process
     *
     * @throws LogicException
     */
    private function waitExtractMedia(Process $process, OutputInterface $output): void
    {
        while ($process->isRunning()) {
            usleep(self::RUNNING_PROCESS_CHECK_INTERVAL_IN_MICROSECONDS);
        }

        $output->writeln($process->getOutput());

        if ($process->getExitCode() !== 0) {
            throw new \Exception(
                sprintf("Extraction of free trial medias failed in the sub-process. Error: %s", $process->getErrorOutput()),
                1
            );
        }
    }
}
