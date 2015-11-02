<?php

namespace Pim\Bundle\ImportExportBundle\Twig;

use Symfony\Component\Form\ChoiceList\View\ChoiceView;
use Symfony\Component\Translation\TranslatorInterface;

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
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

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
        return [
            new \Twig_SimpleFunction(
                'getViolations',
                [$this, 'getViolationsFunction'],
                ['is_safe' => ['html']]
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('normalizeValue', [$this, 'normalizeValueFilter']),
            new \Twig_SimpleFilter('normalizeFieldValue', [$this, 'normalizeFieldValueFilter']),
        ];
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
     * Normalize a complete field to print intelligible data to user.
     * This method takes account of 'choice' type to display label instead of value.
     *
     * @param array $field
     *
     * @return string
     */
    public function normalizeFieldValueFilter(array $field)
    {
        $value = $field['data'];

        if (isset($field['choices'])) {
            foreach ($field['choices'] as $choiceView) {
                if ($choiceView instanceof ChoiceView && $choiceView->value === $value) {
                    return $choiceView->label;
                }
            }
        }

        return $this->normalizeValueFilter($value);
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
        $messages = [];

        foreach ($violations as $violation) {
            if (preg_match(sprintf('/[.]%s$/', $element), $violation->getPropertyPath())) {
                $messages[] = sprintf(
                    '<span class="label label-important">%s</span>',
                    $this->translator->trans($violation->getMessage())
                );
            }
        }

        return implode('&nbsp;', array_unique($messages));
    }
}
