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

namespace Akeneo\Pim\Automation\SuggestData\Domain\Model;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Represents a standard response from a subscription request
 * Holds a subscription id and optional suggested data
 *
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class ProductSubscriptionsResponse implements \Countable
{

    private $collection = [];

    public function __construct(array $collection)
    {
        foreach ($collection as $rawApiResponse) {
            $this->collection[] = new ProductSubscriptionResponse(
                $rawApiResponse
            );
        }
    }

    public function count()
    {
        return count($this->collection);
    }


}
