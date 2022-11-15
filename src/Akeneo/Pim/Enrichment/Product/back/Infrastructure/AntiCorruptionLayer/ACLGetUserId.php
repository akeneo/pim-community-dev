<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\AntiCorruptionLayer;

use Akeneo\Pim\Enrichment\Product\API\ValueObject\UserId;
use Akeneo\Pim\Enrichment\Product\Domain\Query\GetUserId;
use Akeneo\UserManagement\Bundle\Context\UserContext;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ACLGetUserId implements GetUserId
{
    public function __construct(private UserContext $userContext)
    {
    }

    public function getUserId(): UserId
    {
        return UserId::fromId((string) $this->userContext->getUser()?->getId());
    }
}
