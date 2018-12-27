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
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
final class ProductSubscriptionException extends \Exception
{
    /** @var string */
    private const CONSTRAINT_KEY = 'akeneo_franklin_insights.entity.product_subscription.constraint.%s';

    /**
     * @return ProductSubscriptionException
     */
    public static function insufficientCredits(): ProductSubscriptionException
    {
        return new static(sprintf(static::CONSTRAINT_KEY, 'insufficient_credits'));
    }

    /**
     * @return ProductSubscriptionException
     */
    public static function invalidIdentifiersMapping(): ProductSubscriptionException
    {
        return new static(sprintf(static::CONSTRAINT_KEY, 'no_identifiers_mapping'));
    }

    /**
     * @return ProductSubscriptionException
     */
    public static function familyRequired(): ProductSubscriptionException
    {
        return new static(sprintf(static::CONSTRAINT_KEY, 'family_required'));
    }

    /**
     * @return ProductSubscriptionException
     */
    public static function invalidMappedValues(): ProductSubscriptionException
    {
        return new static(sprintf(static::CONSTRAINT_KEY, 'invalid_mapped_values'));
    }
}
