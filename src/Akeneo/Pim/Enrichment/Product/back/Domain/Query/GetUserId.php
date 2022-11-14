<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\back\Domain\Query;

use Akeneo\Pim\Enrichment\Product\back\API\ValueObject\UserId;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetUserId
{
    public function getUserId(): UserId;
}
