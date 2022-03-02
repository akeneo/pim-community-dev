<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory\InMemoryGetEditableLocaleCodes;
use Behat\Behat\Context\Context;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ChannelContext implements Context
{
    public function __construct(private InMemoryGetEditableLocaleCodes $getEditableLocaleCodes)
    {
    }

    /**
     * @Given the :groupName user group has editable permission on the :localeCode locale
     */
    public function theUserGroupHasEditablePermissionOnLocale(string $groupName, string $localeCode): void
    {
        $this->getEditableLocaleCodes->addOwnedCategoryCode($groupName, $localeCode);
    }
}
