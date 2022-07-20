<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Test\Acceptance\Common\ListOfCodes;
use Akeneo\Test\Acceptance\Locale\InMemoryLocaleRepository;
use Akeneo\Test\Channel\Acceptance\InMemory\InMemoryIsLocaleEditable;
use Behat\Behat\Context\Context;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LocaleContext implements Context
{
    public function __construct(
        private InMemoryLocaleRepository $localeRepository,
        private InMemoryIsLocaleEditable $isLocaleEditable
    ) {
    }

    /**
     * @Given the following locales :localeCodes
     */
    public function theFollowingLocale(string $localeCodes)
    {
        $localeCodes = new ListOfCodes($localeCodes);

        foreach ($localeCodes->explode() as $localeCode) {
            $locale = new Locale();
            $locale->setCode($localeCode);
            $this->localeRepository->save($locale);
        }
    }

    /**
     * @Given the :groupName user group has editable permission on the :localeCode locale
     */
    public function theUserGroupHasEditablePermissionOnLocale(string $groupName, string $localeCode): void
    {
        $this->isLocaleEditable->addEditableLocaleCodeForGroup($groupName, $localeCode);
    }
}
