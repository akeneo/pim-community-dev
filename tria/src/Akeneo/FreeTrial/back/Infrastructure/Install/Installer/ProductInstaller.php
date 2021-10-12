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
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProductInstaller implements FixtureInstaller
{
    private ProductBuilderInterface $productBuilder;

    private ObjectUpdaterInterface $updater;

    private SaverInterface $saver;

    private ValidatorInterface $productValidator;

    private FixtureReader $fixturesReader;

    public function __construct(
        FixtureReader $fixturesReader,
        ProductBuilderInterface $productBuilder,
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
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
        foreach ($this->fixturesReader->read() as $productData) {
            $productData['values']['sku'] = [[
                'locale' => null,
                'scope' => null,
                'data' => $productData['identifier'],
            ]];
            $this->addProduct($productData);
        }
    }

    private function addProduct(array $productData): void
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

        $this->saver->save($product);
    }
}
