<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredExport\Application\Query\Selection\QuantifiedAssociations;

use Akeneo\Platform\TailoredExport\Application\Query\Selection\SelectionInterface;

interface QuantifiedAssociationsSelectionInterface extends SelectionInterface
{
    public const ENTITY_TYPE_PRODUCTS = 'products';
    public const ENTITY_TYPE_PRODUCT_MODELS = 'product_models';

    public function isProductsSelection(): bool;

    public function isProductModelsSelection(): bool;

    public function getSeparator(): string;
}
