<?php

declare(strict_types=1);

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Pim\Structure\Bundle\Application\GetAttributeGroup;

use Akeneo\Pim\Structure\Bundle\Domain\Query\Sql\GetAttributeGroupsInterface;
use Akeneo\Pim\Structure\Bundle\infrastructure\Query\Sql\GetAttributeGroups;

final class GetAttributeGroupHandler
{
    public function __construct(
        private readonly GetAttributeGroupsInterface $getAttributeGroups
    ) {
    }

    public function handle(): array
    {
        return $this->getAttributeGroups->all();
    }
}
