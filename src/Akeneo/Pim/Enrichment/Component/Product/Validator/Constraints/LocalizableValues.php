<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableValues extends Constraint
{
    const NON_ACTIVE_LOCALE = '7962b972-e39a-461e-86f2-4900281a72aa';
    const INVALID_LOCALE_FOR_CHANNEL = 'e2239e6b-934b-4791-aca8-9585d03ae43c';

    /** @var string */
    public $nonActiveLocaleMessage = 'The %attribute_code% attribute requires a valid locale. The %invalid_locale% locale does not exist.';

    /** @var string */
    public $invalidLocaleForChannelMessage = 'The %attribute_code% attribute requires a valid locale. The %invalid_locale% locale is not bound to the %channel_code% channel or does not exist.';

    /** @var string */
    public $invalidLocaleSpecificMessage = '"%invalid_locale%" is not part of the available locales for the locale specific attribute "%attribute_code%".';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_localizable_values_validator';
    }
}
