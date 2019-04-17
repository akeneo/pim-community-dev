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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Model\Read;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductSubscriptionResponseCollection
{
    /** @var ProductSubscriptionResponse[] */
    private $responses = [];

    /** @var array */
    private $warnings = [];

    /**
     * @param array $warnings
     */
    public function __construct(array $warnings)
    {
        $this->warnings = $warnings;
    }

    /**
     * @param ProductSubscriptionResponse $response
     */
    public function add(ProductSubscriptionResponse $response): void
    {
        $this->responses[$response->getProductId()->toInt()] = $response;
    }

    /**
     * @param int $index
     *
     * @return ProductSubscriptionResponse|null
     */
    public function get(int $index): ?ProductSubscriptionResponse
    {
        return $this->responses[$index] ?? null;
    }

    /**
     * @return array
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
