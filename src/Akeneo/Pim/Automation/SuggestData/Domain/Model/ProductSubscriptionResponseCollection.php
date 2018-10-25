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

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductSubscriptionResponseCollection
{
    /** @var ProductSubscriptionResponse[] */
    private $responses = [];

    /** @var array */
    private $warnings;

    /**
     * @param array $warnings
     */
    public function __construct(array $warnings)
    {
        $this->warnings = $warnings;
    }

    /**
     * @return ProductSubscriptionResponse[]
     */
    public function responses(): array
    {
        return $this->responses;
    }

    /**
     * @return array
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @param ProductSubscriptionResponse $response
     */
    public function add(ProductSubscriptionResponse $response): void
    {
        $this->responses[] = $response;
    }
}
