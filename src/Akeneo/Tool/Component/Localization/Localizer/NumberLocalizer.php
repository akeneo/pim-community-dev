<?php

namespace Akeneo\Tool\Component\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Factory\NumberFactory;
use Akeneo\Tool\Component\Localization\Validator\Constraints\NumberFormat;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Check and convert if number provided respects the format expected
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberLocalizer implements LocalizerInterface
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var NumberFactory */
    protected $numberFactory;

    /** @var array */
    protected $attributeTypes;

    /**
     * @param ValidatorInterface $validator
     * @param NumberFactory      $numberFactory
     * @param array              $attributeTypes
     */
    public function __construct(
        ValidatorInterface $validator,
        NumberFactory $numberFactory,
        array $attributeTypes
    ) {
        $this->validator = $validator;
        $this->numberFactory = $numberFactory;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function localize($number, array $options = [])
    {
        if (!is_numeric($number)) {
            return $number;
        }

        $options = $this->getOptions($options);

        if (isset($options['locale'])) {
            $numberFormatter = $this->numberFactory->create($options);

            $numberFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
            $numberFormatter->setSymbol(\NumberFormatter::MINUS_SIGN_SYMBOL, '-');

            if (floor($number) != $number) {
                $numberFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
                $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 4);
            }

            return $numberFormatter->format($number);
        }

        $matchesNumber = $this->getMatchesNumber($number);
        if (!isset($matchesNumber['decimal'])) {
            return $number;
        }

        return str_replace(static::DEFAULT_DECIMAL_SEPARATOR, $options['decimal_separator'], $number);
    }

    /**
     * {@inheritdoc}
     */
    public function delocalize($number, array $options = [])
    {
        if (null === $number || '' === $number) {
            return null;
        }

        $matchesNumber = $this->getMatchesNumber($number);
        if (!isset($matchesNumber['decimal']) && is_numeric($number)) {
            return $number;
        }

        if (isset($matchesNumber['decimal'])) {
            return str_replace($matchesNumber['decimal'], static::DEFAULT_DECIMAL_SEPARATOR, $number);
        }

        return (string) $number;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($number, $attributeCode, array $options = [])
    {
        if (null === $number || ''  === $number || is_int($number) || is_float($number)) {
            return null;
        }

        $options = $this->getOptions($options);

        if (isset($options['locale']) && !isset($options['decimal_separator'])) {
            $numberFormatter = $this->numberFactory->create($options);
            $options['decimal_separator'] = $numberFormatter->getSymbol(\NumberFormatter::DECIMAL_SEPARATOR_SYMBOL);
        }

        $constraint = new NumberFormat();
        $constraint->decimalSeparator = $options['decimal_separator'];
        $constraint->path = $attributeCode;

        return $this->validator->validate($number, $constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }

    /**
     * @param string $number
     *
     * @return array
     */
    protected function getMatchesNumber($number)
    {
        preg_match('|\d+((?P<decimal>\D+)\d+)?|', $number, $matches);

        return $matches;
    }

    /**
     * @param array $options
     *
     * @return array
     */
    protected function getOptions(array $options)
    {
        if (isset($options['decimal_separator']) || isset($options['locale'])) {
            return $options;
        }

        return ['decimal_separator' => LocalizerInterface::DEFAULT_DECIMAL_SEPARATOR];
    }
}
