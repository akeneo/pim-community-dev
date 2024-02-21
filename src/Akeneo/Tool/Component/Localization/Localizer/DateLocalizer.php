<?php

namespace Akeneo\Tool\Component\Localization\Localizer;

use Akeneo\Tool\Component\Localization\Factory\DateFactory;
use Akeneo\Tool\Component\Localization\Validator\Constraints\DateFormat;
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

    /** @var DateFactory */
    protected $factory;

    /** @var array */
    protected $attributeTypes;

    /**
     * @param ValidatorInterface $validator
     * @param DateFactory        $factory
     * @param array              $attributeTypes
     */
    public function __construct(
        ValidatorInterface $validator,
        DateFactory $factory,
        array $attributeTypes
    ) {
        $this->validator = $validator;
        $this->factory = $factory;
        $this->attributeTypes = $attributeTypes;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($date, $attributeCode, array $options = [])
    {
        if (null === $date || '' === $date || $date instanceof \DateTime) {
            return null;
        }

        $options = $this->getOptions($options);
        if (isset($options['locale']) && !isset($options['date_format'])) {
            $formatter = $this->factory->create($options);
            $options['date_format'] = $formatter->getPattern();
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
            return null;
        }

        $options = $this->getOptions($options);
        $formatter = $this->factory->create($options);
        $formatter->setLenient(false);

        if (!$date instanceof \DateTime) {
            $timestamp = $formatter->parse($date);
            if (false === $timestamp) {
                return $date;
            }

            $date = new \DateTime();
            $date->setTimestamp($timestamp);
        }

        return $date->format('c');
    }

    /**
     * {@inheritdoc}
     */
    public function localize($date, array $options = [])
    {
        if (null === $date || '' === $date) {
            return null;
        }

        $options = $this->getOptions($options);
        $formatter = $this->factory->create($options);

        $datetime = new \DateTime($date);

        return $formatter->format($datetime);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return in_array($attributeType, $this->attributeTypes);
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
