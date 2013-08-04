<?php

namespace Pim\Bundle\ConfigBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint for locale fallback
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Annotation
 */
class ValidLocaleFallback extends Constraint
{
    /*
     * Violation message for the same fallback as the locale
     *
     * @var string
     */
    public $fallbackTwinLocale = 'Inherited locale must not be the same as the locale';

    /*
     * Violation message for not allowed fallback
     *
     * @var string
     */
    public $fallbackNotAllowed = 'Inherited locale may not be specified for this locale';

    /**
     * {@inheritDoc}
     */
    public function validatedBy()
    {
        return 'pim_config.validator.valid_locale_fallback_validator';
    }

    /**
     * {@inheritDoc}
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
