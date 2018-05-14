<?php

namespace Akeneo\Tool\Component\Localization\Validator\Constraints;

use Akeneo\Tool\Component\Localization\Factory\DateFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Localized date constraint
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateFormatValidator extends ConstraintValidator
{
    /** @var DateFactory */
    protected $factory;

    /**
     * @param DateFactory $factory
     */
    public function __construct(DateFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($date, Constraint $constraint)
    {
        $formatter = $this->factory->create(['date_format' => $constraint->dateFormat]);
        $formatter->setLenient(false);

        $hasSameSeparators = $this->hasSameSeparators($date, $constraint->dateFormat);

        if (false === $formatter->parse($date) || !$hasSameSeparators) {
            $violation = $this->context->buildViolation($constraint->message, [
                '{{ date_format }}' => $constraint->dateFormat
            ]);
            $violation->atPath($constraint->path);

            $violation->addViolation();
        }
    }

    /**
     * As IntlDateFormatter::parse() checks only values and not separators,
     * we check if separators of $date match with separators of $pattern
     *
     * @param string $date
     * @param string $pattern
     *
     * @return bool
     */
    protected function hasSameSeparators($date, $pattern)
    {
        preg_match('|(\W)+|', $date, $dateSeparators);
        preg_match('|(\W)+|', $pattern, $patternSeparators);

        return 0 === count(array_diff($dateSeparators, $patternSeparators));
    }
}
