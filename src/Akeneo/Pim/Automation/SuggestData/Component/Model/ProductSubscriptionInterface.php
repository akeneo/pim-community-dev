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

namespace Akeneo\Pim\Automation\SuggestData\Component\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
interface ProductSubscriptionInterface
{
    /**
     * @return string
     */
    public function getSubscriptionId(): string;

    /**
     * @return ProductInterface
     */
    public function getProduct(): ProductInterface;

    /**
     * @return array
     */
    public function getSuggestedData(): array;

    /**
     * @param array $suggestedData
     *
     * @return ProductSubscriptionInterface
     */
    public function setSuggestedData(array $suggestedData): self;
}
