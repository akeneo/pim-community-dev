<?php

namespace Pim\Component\Localization\Localizer;

use Pim\Component\Localization\Provider\Format\FormatProviderInterface;
use Pim\Component\Localization\Validator\Constraints\DateFormat;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Check if date provided respects the format expected and convert it
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateLocalizer implements LocalizerInterface
{
    /** @var ValidatorInterface */
    protected $validator;

    /** @var FormatProviderInterface */
    protected $formatProvider;

    /** @var array */
    protected $attributeTypes;

    /**
     * @param ValidatorInterface      $validator
     * @param FormatProviderInterface $formatProvider
     * @param array                   $attributeTypes
     */
    public function __construct(
        ValidatorInterface $validator,
        FormatProviderInterface $formatProvider,
        array $attributeTypes
    ) {
        $this->validator      = $validator;
        $this->formatProvider = $formatProvider;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($date, array $options = [], $attributeCode)
    {
        if (null === $date || '' === $date) {
            return null;
        }

        $options = $this->getOptions($options);

        if (isset($options['locale'])) {
            $options['date_format'] = 'Y-m-d'; // TODO with PIM-5146
        }

        $constraint = new DateFormat();
        $constraint->dateFormat = $options['date_format'];
        $constraint->path = $attributeCode;

        return $this->validator->validate($date, $constraint);
    }

    /**
     * {@inheritdoc}
     */
    public function delocalize($date, array $options = [])
    {
        if (null === $date || '' === $date) {
            return $date;
        }

        $options = $this->getOptions($options);

        if (isset($options['locale'])) {
            $options['date_format'] = 'Y-m-d';
        }

        $datetime = $this->getDateTime($date, $options);

        if (false === $datetime) {
            return $date;
        }

        return $datetime->format(static::DEFAULT_DATE_FORMAT);
    }

    /**
     * {@inheritdoc}
     */
    public function localize($date, array $options = [])
    {
        if (null === $date || '' === $date) {
            return $date;
        }

        $options = $this->getOptions($options);

        if (isset($options['locale'])) {
            $options['date_format'] = 'Y-m-d'; // TODO with PIM-5146
        }

        $datetime = new \DateTime();
        $datetime = $datetime->createFromFormat(static::DEFAULT_DATE_FORMAT, $date);

        return $datetime->format($options['date_format']);
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
     *
     * @return array
     */
    protected function getOptions(array $options)
    {
        if (isset($options['locale']) || isset($options['date_format'])) {
            return $options;
        }

        return ['date_format' => LocalizerInterface::DEFAULT_DATE_FORMAT];
    }
}
