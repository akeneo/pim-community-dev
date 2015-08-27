<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to check if localizable value has existing locale, and if not localizable value has no locale
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocalizableValue extends Constraint
{
    /** @var string */
    public $expectedLocaleMessage = 'Product value for attribute "%attribute%" must be defined with a locale';

    /** @var string */
    public $unexpectedLocaleMessage = 'Product value for attribute "%attribute%" must be defined without a locale';

    /** @var string */
    public $inexistingLocaleMessage = 'Inexisting locale "%locale%" is used to localize the value "%attribute%"';

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_localizable_value_validator';
    }

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
