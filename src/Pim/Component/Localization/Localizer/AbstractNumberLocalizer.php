<?php

namespace Pim\Component\Localization\Localizer;

use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractNumberLocalizer implements LocalizerInterface
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var FormatProviderInterface */
    protected $formatProvider;

    /** @var Constraint */
    protected $numberConstraint;

    /** @var array */
    protected $attributeTypes;

    /**
     * @param ValidatorInterface      $validator
     * @param FormatProviderInterface $formatProvider
     * @param Constraint              $numberConstraint
     * @param array                   $attributeTypes
     */
    public function __construct(
        ValidatorInterface $validator,
        FormatProviderInterface $formatProvider,
        Constraint $numberConstraint,
        array $attributeTypes
    ) {
        $this->validator      = $validator;
        $this->formatProvider = $formatProvider;
        $this->attributeTypes = $attributeTypes;
        $this->numberConstraint = $numberConstraint;
    }

    /**
     * {@inheritdoc}
     */
    public function localize($number, array $options = [])
    {
        if (null === $number || '' === $number) {
            return $number;
        }

        $options = $this->getOptions($options);

        if (isset($options['locale'])) {
            $numberFormatter = new \NumberFormatter($options['locale'], \NumberFormatter::DECIMAL);

            if (isset($options['disable_grouping_separator']) && true === $options['disable_grouping_separator']) {
                $numberFormatter->setSymbol(\NumberFormatter::GROUPING_SEPARATOR_SYMBOL, '');
            }

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
            return $number;
        }

        $matchesNumber = $this->getMatchesNumber($number);
        if (!isset($matchesNumber['decimal']) && is_numeric($number)) {
            return (float) $number;
        } else if (isset($matchesNumber['decimal'])) {
            return (float) str_replace($matchesNumber['decimal'], static::DEFAULT_DECIMAL_SEPARATOR, $number);
        }

        return $number;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($number, array $options = [], $attributeCode)
    {
        if (null === $number || ''  === $number) {
            return null;
        }

        $options = $this->getOptions($options);

        if (isset($options['locale']) && !isset($options['decimal_separator'])) {
            $options['decimal_separator'] = $this->formatProvider->getFormat($options['locale'])['decimal_separator'];
        }

        $this->numberConstraint->decimalSeparator = $options['decimal_separator'];
        $this->numberConstraint->path = $attributeCode;

        return $this->validator->validate($number, $this->numberConstraint);
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
