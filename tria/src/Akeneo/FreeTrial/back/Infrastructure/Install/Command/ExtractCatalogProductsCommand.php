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

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\ApiClient\AkeneoPimClientBuilder;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Akeneo\Pim\ApiClient\Search\Operator;
use Akeneo\Pim\ApiClient\Search\SearchBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ExtractCatalogProductsCommand extends Command
{
    use InstallCatalogTrait;

    private array $mediaFileAttributes = [];
    private array $downloadedMediaFiles = [];
    private array $productModelsAttributes = [];

    protected function configure()
    {
        $this
            ->setName('akeneo:free-trial:extract-products')
            ->setDescription('Extract data for the products and product models from a PIM to build the Free-Trial catalog.')
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
        $io->title('Extract data and media-files for all products and product-models.');

        $io->writeln('Retrieve media files attributes...');
        $apiClient = $this->buildApiClient($input);
        $this->mediaFileAttributes = $this->retrieveMediaFilesAttributes($apiClient);

        $this->clearExtractionFiles();

        $this->extractProductModels($apiClient, $io);
        $this->extractProducts($apiClient, $io);

        return 0;
    }

    private function retrieveMediaFilesAttributes(AkeneoPimClientInterface $apiClient): array
    {
        $searchBuilder = new SearchBuilder();
        $searchBuilder->addFilter('type', Operator::IN, ['pim_catalog_image', 'pim_catalog_file']);

        $attributes = $apiClient->getAttributeApi()->all(100, ['search' => $searchBuilder->getFilters()]);

        $mediaFilesAttributes = [];
        foreach ($attributes as $attribute) {
            $mediaFilesAttributes[] = $attribute['code'];
        }

        if (empty($mediaFilesAttributes)) {
            throw new \Exception('No media files attributes found.');
        }

        return $mediaFilesAttributes;
    }

    private function clearExtractionFiles(): void
    {
        system('rm -rf -- ' . escapeshellarg($this->getMediaFilesFixturesDirectoryPath()));
        mkdir($this->getMediaFilesFixturesDirectoryPath());
        file_put_contents($this->getMediaFilesFixturesPath(), '');
        file_put_contents($this->getProductsFixturesPath(), '');
        file_put_contents($this->getProductsAssociationsFixturesPath(), '');
        file_put_contents($this->getProductModelsFixturesPath(), '');
        file_put_contents($this->getProductModelsAssociationsFixturesPath(), '');
    }

    private function extractProducts(AkeneoPimClientInterface $apiClient, SymfonyStyle $io): void
    {
        $io->section('Extract products.');
        $productApi = $apiClient->getProductApi();
        $products = iterator_to_array($productApi->all());

        $progress = new ProgressBar($io, count($products));
        $progress->start();

        foreach ($products as $product) {
            $this->extractProductAssociations($product);
            $product = $this->cleanProductData($product);
            file_put_contents($this->getProductsFixturesPath(), json_encode($product) . PHP_EOL, FILE_APPEND);
            $this->extractProductMediaFiles($apiClient, $product['values']);
            $progress->advance();
        }

        $progress->finish();
    }

    private function extractProductModels(AkeneoPimClientInterface $apiClient, SymfonyStyle $io): void
    {
        $io->section('Extract product models.');
        $productModelApi = $apiClient->getProductModelApi();

        $productModels = iterator_to_array($productModelApi->all());

        $progress = new ProgressBar($io, count($productModels));
        $progress->start();

        // Put the sub-product-models last of the list to extract their parent first
        usort($productModels, fn ($pm1, $pm2) => (isset($pm1['parent']) ? 1 : 0) - (isset($pm2['parent']) ? 1 : 0));

        foreach ($productModels as $productModel) {
            $this->productModelsAttributes[$productModel['code']] = isset($productModel['parent'])
                ? array_merge($this->productModelsAttributes[$productModel['parent']], array_keys($productModel['values']))
                : array_keys($productModel['values']);

            $this->extractProductModelAssociations($productModel);
            $productModel = $this->cleanProductData($productModel);
            file_put_contents($this->getProductModelsFixturesPath(), json_encode($productModel) . PHP_EOL, FILE_APPEND);
            $this->extractProductMediaFiles($apiClient, $productModel['values']);
            $progress->advance();
        }

        $progress->finish();
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

    private function cleanProductData(array $product): array
    {
        unset($product['_links']);
        unset($product['created']);
        unset($product['updated']);
        unset($product['associations']);
        unset($product['quantified_associations']);

        foreach ($product['values'] as $attribute => $values) {
            // Remove values of attributes that belong to the parent.
            if (isset($product['parent']) && $this->isAttributeFromProductModel($attribute, $product['parent'])) {
                unset($product['values'][$attribute]);
                continue;
            }
            foreach ($values as $index => $value) {
                if (isset($value['_links'])) {
                    unset($product['values'][$attribute][$index]['_links']);
                }
            }
        }

        return $product;
    }

    private function extractProductMediaFiles(AkeneoPimClientInterface $apiClient, array $productValues): void
    {
        foreach ($productValues as $attribute => $values) {
            if (!in_array($attribute, $this->mediaFileAttributes)) {
                continue;
            }
            foreach ($values as $value) {
                $this->downloadMediaFile($apiClient, $value['data']);
            }
        }
    }

    private function downloadMediaFile(AkeneoPimClientInterface $apiClient, string $mediaFileIdentifier): void
    {
        // Because some images are linked to several products
        if (in_array($mediaFileIdentifier, $this->downloadedMediaFiles)) {
            return;
        }

        $mediaFileApi = $apiClient->getProductMediaFileApi();
        $mediaFile = $mediaFileApi->get($mediaFileIdentifier);
        unset($mediaFile['_links']);
        $mediaFileContent = $mediaFileApi->download($mediaFileIdentifier)->getBody();

        // Example of media-file identifier: "b/c/5/5/bc55975a5ddf573608613fead6ef3d4476839642_P00395577_b2.jpeg"
        // So it needs to create sub-directories "b/c/5/5/" to store the file.
        $directoryPath = $this->getMediaFilesFixturesDirectoryPath();
        $subDirectories = explode('/', $mediaFileIdentifier);
        array_pop($subDirectories);
        foreach ($subDirectories as $subDirectory) {
            $directoryPath .= '/' . $subDirectory;
            if (!is_dir($directoryPath)) {
                mkdir($directoryPath);
            }
        }

        file_put_contents($this->getMediaFilesFixturesPath(), json_encode($mediaFile) . PHP_EOL, FILE_APPEND);
        file_put_contents($this->getMediaFilesFixturesDirectoryPath() . '/' . $mediaFileIdentifier, $mediaFileContent);

        $this->downloadedMediaFiles[] = $mediaFileIdentifier;
    }

    private function extractProductAssociations($product): void
    {
        if (!$this->hasAssociation($product['associations'] ?? [])) {
            return;
        }

        $productAssociationsData = [
            'identifier' => $product['identifier'],
            'associations' => $product['associations'],
        ];

        file_put_contents($this->getProductsAssociationsFixturesPath(), json_encode($productAssociationsData) . PHP_EOL, FILE_APPEND);
    }

    private function extractProductModelAssociations($productModel): void
    {
        if (!$this->hasAssociation($productModel['associations'] ?? [])) {
            return;
        }

        $productModelAssociationsData = [
            'code' => $productModel['code'],
            'associations' => $productModel['associations'],
        ];

        file_put_contents($this->getProductModelsAssociationsFixturesPath(), json_encode($productModelAssociationsData) . PHP_EOL, FILE_APPEND);
    }

    private function hasAssociation(array $associations): bool
    {
        foreach ($associations as $association) {
            if (!empty($association['products']) || !empty($association['product_models']) || !empty($association['groups'])) {
                return true;
            }
        }

        return false;
    }

    private function isAttributeFromProductModel(string $attribute, string $productModelCode): bool
    {
        if (!isset($this->productModelsAttributes[$productModelCode])) {
            throw new \Exception(sprintf('There are no attributes for the product model "%s"', $productModelCode));
        }

        return in_array($attribute, $this->productModelsAttributes[$productModelCode]);
    }
}
