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

namespace Akeneo\Pim\Automation\SuggestData\Component\Service;

use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductCommand;
use Akeneo\Pim\Automation\SuggestData\Application\ProductSubscription\Command\SubscribeProductHandler;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class SubscribeSingleProduct
{
    /** @var SubscribeProductHandler */
    private $subscribeProductHandler;

    /**
     * @param SubscribeProductHandler $subscribeProductHandler
     */
    public function __construct(SubscribeProductHandler $subscribeProductHandler)
    {
        $this->subscribeProductHandler = $subscribeProductHandler;
    }

    /**
     * @param int $productId
     */
    public function subscribe(int $productId)
    {
        $command = new SubscribeProductCommand($productId);
        $this->subscribeProductHandler->handle($command);
    }
}
