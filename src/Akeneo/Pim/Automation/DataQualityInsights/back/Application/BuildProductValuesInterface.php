<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Application;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;

interface BuildProductValuesInterface
{
    public function buildTextareaValues(ProductId $productId): array;

    public function buildTextValues(ProductId $productId): array;

    public function buildTitleValues(ProductId $productId): array;
}
