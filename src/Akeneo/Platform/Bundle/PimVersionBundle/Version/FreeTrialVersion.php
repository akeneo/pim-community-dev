<?php

namespace Akeneo\Platform\Bundle\PimVersionBundle\Version;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class FreeTrialVersion implements PimVersion
{
    /** @staticvar string */
    const VERSION_CODENAME = 'Free Trial Edition';

    /** @staticvar string */
    const EDITION_NAME = 'Free Trial Edition';

    /** @staticvar string **/
    private const EDITION_CODE = 'pim_trial_instance';

    public function versionCodename(): string
    {
        return self::VERSION_CODENAME;
    }

    public function editionName(): string
    {
        return self::EDITION_NAME;
    }

    public function isSaas(): bool
    {
        return false;
    }

    public function isEditionCode(string $editionCode): bool
    {
        return $editionCode === self::EDITION_CODE;
    }
}
