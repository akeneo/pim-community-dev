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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
final class JobInstanceNames
{
    /** @var string */
    public const REMOVE_ATTRIBUTES_FROM_MAPPING = 'franklin_insights_remove_attribute_from_mapping';

    /** @var string */
    public const REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING = 'franklin_insights_remove_option_from_mapping';

    /** @var string */
    public const SUBSCRIBE_PRODUCTS = 'franklin_insights_subscribe_products';

    /** @var string */
    public const UNSUBSCRIBE_PRODUCTS = 'franklin_insights_unsubscribe_products';

    /** @var string */
    public const FETCH_PRODUCTS = 'franklin_insights_fetch_products';

    /** @var string */
    public const RESUBSCRIBE_PRODUCTS = 'franklin_insights_resubscribe_products';

    /** @var string */
    public const IDENTIFY_PRODUCTS_TO_RESUBSCRIBE = 'franklin_insights_identify_products_to_resubscribe';

    /** @var string */
    public const SYNCHRONIZE = 'franklin_insights_synchronize';
}
