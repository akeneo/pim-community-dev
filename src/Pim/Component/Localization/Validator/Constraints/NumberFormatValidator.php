<?php

namespace Pim\Component\Localization\Validator\Constraints;

use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Localized number constraint
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFormatValidator extends ConstraintValidator
{
    /** @var array */
    protected $decimalSeparators;

    /** @var TranslatorInterface */
    protected $translator;

    /**
     * @param array $decimalSeparators
     */
    public function __construct(array $decimalSeparators, TranslatorInterface $translator)
    {
        $this->decimalSeparators = $decimalSeparators;
        $this->translator        = $translator;
    }

    /**
     * Returns the decimal separator label for the constraint translation.
     *
     * @param Constraint $constraint
     *
     * @return mixed
     */
    public function getLabel(Constraint $constraint)
    {
        $label = $constraint->decimalSeparator;
        if (isset($this->decimalSeparators[$label])) {
            $label = $this->translator->trans($this->decimalSeparators[$label], [], 'validators');
        }

        return $label;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($number, Constraint $constraint)
    {
        preg_match('|\d+((?P<decimal>\D+)\d+)?|', $number, $matches);

        if (isset($matches['decimal']) && $matches['decimal'] !== $constraint->decimalSeparator) {
            $violation = $this->context->buildViolation(
                $constraint->message,
                ['{{ decimal_separator }}' => $this->getLabel($constraint)]
            );

            $violation->atPath($constraint->path);

            $violation->addViolation();
        }
    }
}
