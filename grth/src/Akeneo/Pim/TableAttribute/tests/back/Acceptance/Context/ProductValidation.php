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

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\Context;

use Akeneo\Test\Acceptance\Catalog\Context\ProductValidation as BaseProductValidation;
use Akeneo\Test\Common\EntityWithValue\Builder\Product;
use Behat\Behat\Context\Context;

final class ProductValidation implements Context
{
    private BaseProductValidation $decoratedProductValidation;
    private Product $productBuilder;

    public function __construct(
        BaseProductValidation $decoratedProductValidation,
        Product $productBuilder
    ) {
        $this->decoratedProductValidation = $decoratedProductValidation;
        $this->productBuilder = $productBuilder;
    }

    /**
     * @When a product is created with too many rows
     */
    public function aProductIsCreatedWithTooManyRows(): void
    {
        $this->productBuilder->withIdentifier('foo');

        $normalizedNutrition = [];
        for ($i = 0; $i < 101; $i++) {
            $normalizedNutrition[] = ['ingredient' => sprintf('ingredient_%d', $i), 'quantity' => 0];
        }
        $this->productBuilder->withValue('nutrition', $normalizedNutrition);

        $this->decoratedProductValidation->setUpdatedProduct($this->productBuilder->build(false));
    }

    /**
     * @When a product is created with too many cells
     */
    public function aProductIsCreatedWithTooManyCells(): void
    {
        $this->productBuilder->withIdentifier('foo');

        $normalizedNutrition = [];
        for ($i = 0; $i < 1000; $i++) {
            $normalizedNutrition[] = ['ingredient' => sprintf('ingredient_%d', $i), 'quantity' => 0];
        }
        $this->productBuilder->withValue('nutrition', $normalizedNutrition);

        $normalizedPackaging = [];
        for ($i = 0; $i < 3001; $i++) {
            $normalizedPackaging[] = ['parcel' => sprintf('parcel_%d', $i), 'length' => 1];
        }
        $this->productBuilder->withValue('packaging', $normalizedPackaging);

        $this->decoratedProductValidation->setUpdatedProduct($this->productBuilder->build(false));
    }
}
