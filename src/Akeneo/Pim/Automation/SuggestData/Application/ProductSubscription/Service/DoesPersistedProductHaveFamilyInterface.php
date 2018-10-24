<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Checks if the persisted version of a product has a family.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
interface DoesPersistedProductHaveFamilyInterface
{
    /**
     * @param ProductInterface $product
     *
     * @return bool
     */
    public function check(ProductInterface $product): bool;
}
