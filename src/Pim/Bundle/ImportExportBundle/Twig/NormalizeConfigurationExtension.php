<?php

namespace Pim\Bundle\ImportExportBundle\Twig;

/**
 * Twig extension to normalize configuration values
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizeConfigurationExtension extends \Twig_Extension
{
    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'pim_ie_normalize_configuration';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('getViolations', array($this, 'getViolationsFunction')),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('normalizeValue', array($this, 'normalizeValueFilter')),
            new \Twig_SimpleFilter('normalizeKey', array($this, 'normalizeKeyFilter')),
        );
    }

    /**
     * Normalize value to print intelligible data to user
     *
     * @param mixed $value
     *
     * @return string
     */
    public function normalizeValueFilter($value)
    {
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        if (!$value) {
            return 'N/A';
        }

        return (string) $value;
    }

    /**
     * Normalize key to print intelligible data to user
     *
     * @param string $key
     *
     * @return string
     */
    public function normalizeKeyFilter($key)
    {
        return ucfirst(strtolower(preg_replace('/([A-Z])/', ' ${1}', $key)));
    }

    public function getViolationsFunction($violations, $step, $element, $field)
    {
        $currentPropertyPath = sprintf('jobDefinition.steps[%d].%s.%s', $step, strtolower($element), $field);
        $messages            = array();

        foreach ($violations as $violation) {
            if ($currentPropertyPath === $violation->getPropertyPath()) {
                $messages[] = $violation->getMessage();
            }
        }

        return join(' ', $messages);
    }
}
