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

namespace Akeneo\FreeTrial\Infrastructure\Install\Command\Extractor;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Symfony\Component\Console\Style\StyleInterface;

final class ExtractMediaFiles
{
    use InstallCatalogTrait;

    private AkeneoPimClientInterface $apiClient;

    private StyleInterface $io;

    public function __construct(AkeneoPimClientInterface $apiClient, StyleInterface $io)
    {
        $this->apiClient = $apiClient;
        $this->io = $io;
    }

    public function __invoke(): void
    {
        $this->io->section('Extract media files');

        $mediaFilesApi = $this->apiClient->getProductMediaFileApi();
        $this->io->progressStart($mediaFilesApi->listPerPage(1, true)->getCount());

        file_put_contents($this->getMediaFilesFixturesPath(), '');

        foreach ($mediaFilesApi->all() as $mediaFile) {
            unset($mediaFile['_links']);

            $mediaFileContent = $mediaFilesApi->download($mediaFile['code'])->getBody();
            $mediaFile['hash'] = sha1(strval($mediaFileContent));

            file_put_contents($this->getMediaFilesFixturesPath(), json_encode($mediaFile) . PHP_EOL, FILE_APPEND);
            $this->io->progressAdvance(1);
        }

        $this->io->progressFinish();
    }
}
