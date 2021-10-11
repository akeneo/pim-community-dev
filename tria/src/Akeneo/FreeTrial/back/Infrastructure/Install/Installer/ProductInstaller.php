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

namespace Akeneo\FreeTrial\Infrastructure\Install\Installer;

use Akeneo\FreeTrial\Infrastructure\Install\Reader\FixtureReader;
use Akeneo\Pim\Enrichment\Component\Product\Builder\ProductBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\BulkSaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProductInstaller implements FixtureInstaller
{
    private const BATCH_SIZE = 100;

    private ProductBuilderInterface $productBuilder;

    private ObjectUpdaterInterface $updater;

    private BulkSaverInterface $saver;

    private ValidatorInterface $productValidator;

    private FixtureReader $fixturesReader;

    public function __construct(
        FixtureReader $fixturesReader,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $updater,
        BulkSaverInterface $saver,
        ValidatorInterface $productValidator
    ) {
        $this->productBuilder = $productBuilder;
        $this->updater = $updater;
        $this->saver = $saver;
        $this->productValidator = $productValidator;
        $this->fixturesReader = $fixturesReader;
    }

    public function install(): void
    {
        $products = [];
        foreach ($this->fixturesReader->read() as $productData) {
            $productData['values']['sku'] = [[
                'locale' => null,
                'scope' => null,
                'data' => $productData['identifier'],
            ]];
            $products[] = $this->createProduct($productData);

            if (count($products) % self::BATCH_SIZE === 0) {
                $this->saver->saveAll($products);
                $products = [];
            }
        }

        if (!empty($products)) {
            $this->saver->saveAll($products);
        }
    }

    private function createProduct(array $productData): ProductInterface
    {
        $product = $this->productBuilder->createProduct();
        $this->updater->update($product, $productData);

        $violations = $this->productValidator->validate($product, null, ['Default', 'api']);
        if (0 !== $violations->count()) {
            throw new \Exception(sprintf(
                'validation failed on product "%s" with message: "%s"',
                $productData['identifier'],
                iterator_to_array($violations)[0]->getMessage()
            ));
        }

        return $product;
    }
}
