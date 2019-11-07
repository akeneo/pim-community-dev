<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableValues extends Constraint
{
    /** @var string */
    public $nonActiveLocaleMessage = 'Attribute "%attribute_code%" expects an existing and activated locale, "%invalid_locale%" given.';

    /** @var string */
    public $invalidLocaleForChannelMessage = 'Attribute "%attribute_code%" expects a valid locale, "%invalid_locale%" is not bound to channel "%channel_code%".';

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
