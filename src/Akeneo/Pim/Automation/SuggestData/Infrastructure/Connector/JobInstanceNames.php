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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Connector;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
final class JobInstanceNames
{
    /** @var string */
    public const REMOVE_ATTRIBUTES_FROM_MAPPING = 'suggest_data_remove_attribute_from_mapping';

    /** @var string */
    public const REMOVE_ATTRIBUTE_OPTION_FROM_MAPPING = 'suggest_data_remove_attribute_option_from_mapping';

    /** @var string */
    public const SUBSCRIBE_PRODUCTS = 'suggest_data_subscribe_products';

    /** @var string */
    public const UNSUBSCRIBE_PRODUCTS = 'suggest_data_unsubscribe_products';

    /** @var string */
    public const FETCH_PRODUCTS = 'suggest_data_fetch_products';
}
