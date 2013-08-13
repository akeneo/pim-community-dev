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
            new \Twig_SimpleFunction(
                'getViolations',
                array($this, 'getViolationsFunction'),
                array('is_safe' => array('html'))
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('normalizeValue', array($this, 'normalizeValueFilter')),
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

        if (null === $value) {
            return 'N/A';
        }

        return (string) $value;
    }

    /**
     * Get the violations from a collection of violations that concern
     * a given field (e.g: channel) of an element (e.g: reader) of a step (e.g: 0)
     *
     * @param ConstraintViolationList $violations
     * @param integer                 $step
     * @param string                  $element
     * @param string                  $field
     *
     * @return string The violation messages separated by a space character
     */
    public function getViolationsFunction($violations, $step, $element, $field)
    {
        $currentPropertyPath = sprintf('jobDefinition.steps[%d].%s.%s', $step, strtolower($element), $field);
        $messages            = array();

        foreach ($violations as $violation) {
            if ($currentPropertyPath === $violation->getPropertyPath()) {
                $messages[] = $violation->getMessage();
            }
        }

        if (count($messages)) {
            $spanStart = '<span class="label label-important">';
            $spanEnd   = '</span>';

            return $spanStart . join("$spanEnd $spanStart", $messages) . $spanEnd;
        }
    }
}
