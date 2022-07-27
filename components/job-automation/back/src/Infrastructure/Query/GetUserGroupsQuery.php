<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\JobAutomation\Infrastructure\Query;

use Akeneo\Platform\JobAutomation\Domain\Query\GetUserGroupsQueryInterface;

final class GetUserGroupsQuery implements GetUserGroupsQueryInterface
{
    public function execute(): array
    {
        return [
            'IT Support',
            'Manager',
            'Furniture manager',
            'Clothes manager',
            'Redactor',
            'English translator',
            'SAP Connection',
            'Alkemics Connection',
            'Translations.com Connection',
            'Magento Connection'
        ];
    }
}
