<?php

namespace Pim\Component\Localization\Localizer;

use Pim\Component\Localization\Exception\FormatLocalizerException;
use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Check if date provided respects the format expected and convert it
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateLocalizer implements LocalizerInterface
{
    /** @var array */
    protected $attributeTypes;

    /** @var FormatProviderInterface */
    protected $formatProvider;

    /**
     * @param FormatProviderInterface $formatProvider
     * @param array                   $attributeTypes
     */
    public function __construct(FormatProviderInterface $formatProvider, array $attributeTypes)
    {
        $this->formatProvider = $formatProvider;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function isValid($date, array $options = [], $attributeCode)
    {
        if (null === $date || '' === $date) {
            return true;
        }

        $this->checkOptions($options);

        $datetime = $this->getDateTime($date, $options);
        if (false === $datetime) {
            throw new FormatLocalizerException($attributeCode, $options['date_format']);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function convertLocalizedToDefault($date, array $options = [])
    {
        $this->checkOptions($options);

        if (null === $date || '' === $date) {
            return $date;
        }

        $datetime = $this->getDateTime($date, $options);

        return $datetime->format(static::DEFAULT_DATE_FORMAT);
    }

    /**
     * {@inheritdoc}
     */
    public function convertDefaultToLocalized($date, array $options = [])
    {
        $this->checkOptions($options);

        if (null === $date || '' === $date) {
            return $date;
        }

        $datetime = new \DateTime();
        $datetime = $datetime->createFromFormat(static::DEFAULT_DATE_FORMAT, $date);

        return $datetime->format($options['date_format']);
    }

    /**
     * {@inheritdoc}
     */
    public function convertDefaultToLocalizedFromLocale($date, $locale)
    {
        if (null === $date || '' === $date) {
            return $date;
        }

        $format = $this->formatProvider->getFormat($locale);

        $datetime = new \DateTime();
        $datetime = $datetime->createFromFormat(static::DEFAULT_DATE_FORMAT, $date);

        return $datetime->format($format);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
    }

    /**
     * Get a \DateTime from date and format date provided
     *
     * @param string $date
     * @param array  $options
     *
     * @return \DateTime|false
     */
    protected function getDateTime($date, array $options)
    {
        $datetime = new \DateTime();

        return $datetime->createFromFormat($options['date_format'], $date);
    }

    /**
     * @param array $options
     */
    protected function checkOptions(array $options)
    {
        if (!isset($options['date_format']) || '' === $options['date_format']) {
            throw new MissingOptionsException('The option "date_format" do not exist.');
        }
    }
}
