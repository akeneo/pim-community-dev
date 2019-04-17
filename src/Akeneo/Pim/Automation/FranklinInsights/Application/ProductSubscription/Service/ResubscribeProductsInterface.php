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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\ProductSubscription\Service;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\ValueObject\ProductId;

/**
 * @author Mathias Métayer <mathias.metayer@akeneo.com>
 */
interface ResubscribeProductsInterface
{
    /**
     * @param ProductId[] $productIds
     */
    public function process(array $productIds): void;
}
