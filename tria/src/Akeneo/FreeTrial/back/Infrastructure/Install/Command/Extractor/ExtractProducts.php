<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\Install\Command\Extractor;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;
use Akeneo\Pim\ApiClient\AkeneoPimClientInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class ExtractProducts
{
    use InstallCatalogTrait;

    private AkeneoPimClientInterface $apiClient;

    private OutputInterface $output;

    private array $productModelsAttributes = [];

    public function __construct(AkeneoPimClientInterface $apiClient, OutputInterface $output)
    {
        $this->apiClient = $apiClient;
        $this->output = $output;
    }

    public function __invoke(): void
    {
        $this->clearExtractionFiles();
        $this->extractProductModels();
        $this->extractProducts();
    }

    private function extractProductModels(): void
    {
        $this->output->write('Extract product models... ');

        $productModelApi = $this->apiClient->getProductModelApi();
        $productModels = iterator_to_array($productModelApi->all());

        // Put the sub-product-models last of the list to extract their parent first
        usort($productModels, fn ($pm1, $pm2) => (isset($pm1['parent']) ? 1 : 0) - (isset($pm2['parent']) ? 1 : 0));

        foreach ($productModels as $productModel) {
            $this->productModelsAttributes[$productModel['code']] = isset($productModel['parent'])
                ? array_merge($this->productModelsAttributes[$productModel['parent']], array_keys($productModel['values']))
                : array_keys($productModel['values']);

            $this->extractProductModelAssociations($productModel);
            $productModel = $this->cleanProductData($productModel);
            file_put_contents($this->getProductModelFixturesPath(), json_encode($productModel) . PHP_EOL, FILE_APPEND);
        }

        $this->output->writeln(sprintf('%d product models extracted', count($productModels)));
    }

    private function extractProducts(): void
    {
        $this->output->write('Extract products... ');

        $productApi = $this->apiClient->getProductApi();
        $productsCount = 0;

        foreach ($productApi->all() as $product) {
            $this->extractProductAssociations($product);
            $product = $this->cleanProductData($product);
            file_put_contents($this->getProductFixturesPath(), json_encode($product) . PHP_EOL, FILE_APPEND);
            $productsCount++;
        }

        $this->output->writeln(sprintf('%d products extracted', $productsCount));
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

        file_put_contents($this->getProductAssociationFixturesPath(), json_encode($productAssociationsData) . PHP_EOL, FILE_APPEND);
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

        file_put_contents($this->getProductModelAssociationFixturesPath(), json_encode($productModelAssociationsData) . PHP_EOL, FILE_APPEND);
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

    private function cleanProductData(array $product): array
    {
        unset($product['_links']);
        unset($product['created']);
        unset($product['updated']);
        unset($product['associations']);
        unset($product['quantified_associations']);
        unset($product['groups']);

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

    private function isAttributeFromProductModel(string $attribute, string $productModelCode): bool
    {
        if (!isset($this->productModelsAttributes[$productModelCode])) {
            throw new \Exception(sprintf('There are no attributes for the product model "%s"', $productModelCode));
        }

        return in_array($attribute, $this->productModelsAttributes[$productModelCode]);
    }

    private function clearExtractionFiles(): void
    {
        file_put_contents($this->getProductFixturesPath(), '');
        file_put_contents($this->getProductAssociationFixturesPath(), '');
        file_put_contents($this->getProductModelFixturesPath(), '');
        file_put_contents($this->getProductModelAssociationFixturesPath(), '');
    }
}
