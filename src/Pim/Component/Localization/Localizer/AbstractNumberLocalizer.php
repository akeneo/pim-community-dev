<?php

namespace Pim\Component\Localization\Localizer;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractNumberLocalizer implements LocalizerInterface
{
    const DEFAULT_DECIMAL_SEPARATOR = '.';

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
     * @param mixed $number
     * @param array $options
     *
     * @return bool
     */
    protected function isValidNumber($number, array $options = [])
    {
        if (null === $number || ''  === $number) {
            return true;
        }

        $this->checkOptions($options);

        preg_match('|\d+((?P<decimal>\D{1})\d+)?|', $number, $matches);
        if (isset($matches['decimal']) && $matches['decimal'] !== $options['decimal_separator']) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function convertNumber($number)
    {
        if (null === $number || ''  === $number) {
            return $number;
        }

        $replacement = sprintf('${1}%s${2}', static::DEFAULT_DECIMAL_SEPARATOR);

        return preg_replace('|(\d+)\D{1}(\d+)|', $replacement, $number);
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
        $resolver->setRequired(['decimal_separator'])
            ->setAllowedTypes('decimal_separator', 'string');

        $resolver->resolve($options);
    }
}
