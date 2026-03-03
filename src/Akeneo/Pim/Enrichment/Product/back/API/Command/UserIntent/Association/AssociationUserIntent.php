<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AssociationUserIntent extends UserIntent
{
    public function associationType(): string;
}
