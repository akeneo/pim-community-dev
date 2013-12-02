<?php

namespace Pim\Bundle\ImportExportBundle\Twig;

/**
 * Twig extension to normalize configuration values
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NormalizeConfigurationExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
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
     * @param string                  $element
     *
     * @return string The violation messages separated by a space character
     */
    public function getViolationsFunction($violations, $element)
    {
        $messages = array();

        foreach ($violations as $violation) {
            if (preg_match(sprintf('/[.]%s$/', $element), $violation->getPropertyPath())) {
                $messages[] = sprintf('<span class="label label-important">%s</span>', $violation->getMessage());
            }
        }

        return join('', $messages);
    }
}
