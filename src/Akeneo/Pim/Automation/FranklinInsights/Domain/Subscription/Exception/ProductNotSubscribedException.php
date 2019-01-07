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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Exception;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
class ProductNotSubscribedException extends \Exception
{
    /** @var string */
    private const CONSTRAINT_KEY = 'akeneo_franklin_insights.entity.product_subscription.constraint.%s';

    /**
     * @param int $productId
     *
     * @return ProductNotSubscribedException
     */
    public static function notSubscribed(int $productId): ProductNotSubscribedException
    {
        return new static(sprintf(static::CONSTRAINT_KEY, 'not_subscribed'));
    }
}
