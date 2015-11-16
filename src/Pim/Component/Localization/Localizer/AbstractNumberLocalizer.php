<?php

namespace Pim\Component\Localization\Localizer;

use Pim\Component\Localization\Exception\FormatLocalizerException;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractNumberLocalizer implements LocalizerInterface
{
    /** @var array */
    protected $attributeTypes;

    /**
     * @param array $attributeTypes
     */
    public function __construct(array $attributeTypes)
    {
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function localize($number, array $options = [])
    {
        if (null === $number || '' === $number) {
            return $number;
        }

        $this->checkOptions($options);

        if (isset($options['decimal_separator'])) {
            $matchesNumber = $this->getMatchesNumber($number);
            if (!isset($matchesNumber['decimal'])) {
                return $number;
            }

            return str_replace(static::DEFAULT_DECIMAL_SEPARATOR, $options['decimal_separator'], $number);
        }

        if (isset($options['locale'])) {
            $numberFormatter = new \NumberFormatter($options['locale'], \NumberFormatter::DECIMAL);
            $numberFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');

            if (floor($number) != $number) {
                $numberFormatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, 2);
                $numberFormatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, 4);
            }

            return $numberFormatter->format($number);
        }

        return $number;
    }

    /**
     * {@inheritdoc}
     */
    public function delocalize($number, array $options = [])
    {
        if (null === $number || '' === $number) {
            return $number;
        }

        $matchesNumber = $this->getMatchesNumber($number);
        if (!isset($matchesNumber['decimal'])) {
            return $number;
        }

        return str_replace($matchesNumber['decimal'], static::DEFAULT_DECIMAL_SEPARATOR, $number);
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($number, array $options = [], $attributeCode)
    {
        if (null === $number || ''  === $number) {
            return true;
        }

        $this->checkOptions($options);

        $matchesNumber = $this->getMatchesNumber($number);
        if (isset($matchesNumber['decimal']) && $matchesNumber['decimal'] !== $options['decimal_separator']) {
            throw new FormatLocalizerException($attributeCode, $options['decimal_separator']);
        }

        return true;
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
     */
    protected function checkOptions(array $options)
    {
        if (!isset($options['decimal_separator']) || '' === $options['decimal_separator']) {
            throw new MissingOptionsException('The option "decimal_separator" do not exist.');
        }
    }
}
