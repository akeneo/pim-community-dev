<?php

namespace Akeneo\Pim\Structure\Bundle\Application\SwitchMainIdentifier;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CommunityPublishedProductExists implements PublishedProductExists
{
    public function __invoke(): bool
    {
        return false;
    }
}
