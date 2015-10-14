<?php

namespace Pim\Component\Localization\Localizer;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Check if date provided respects the format expected and convert it
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateLocalizer implements LocalizerInterface
{
    const DEFAULT_DATE_FORMAT = 'Y/m/d';

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
    public function isValid($date, array $options = [])
    {
        if (null === $date || '' === $date) {
            return true;
        }

        $this->checkOptions($options);

        try {
            $datetime = new \DateTime();
            if(false === $datetime->createFromFormat($options['format_date'], $date)) {
                return false;
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function convertLocalizedToDefault($date, array $options = [])
    {
        if (null === $date || '' === $date) {
            return $date;
        }

        $this->checkOptions($options);

        $datetime = new \DateTime();
        $datetime = $datetime->createFromFormat($options['format_date'], $date);

        return $datetime->format(static::DEFAULT_DATE_FORMAT);
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
     */
    protected function checkOptions(array $options)
    {
        $resolver = new OptionsResolver();
        $resolver->setRequired(['format_date'])
            ->setAllowedTypes('format_date', 'string');

        $resolver->resolve($options);
    }
}
