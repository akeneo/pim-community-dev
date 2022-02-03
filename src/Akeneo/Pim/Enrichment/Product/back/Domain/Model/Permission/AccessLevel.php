<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Domain\Model\Permission;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AccessLevel
{
    public const OWN_PRODUCTS = 'OWN_PRODUCTS';
    public const EDIT_ITEMS = 'EDIT_ITEMS';
}
