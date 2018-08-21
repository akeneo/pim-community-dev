<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Api\Fetch;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\ProductSubscriptionsResponse;

/**
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
interface FetchApiInterface
{
    public function fetch(): ProductSubscriptionsResponse;
}
