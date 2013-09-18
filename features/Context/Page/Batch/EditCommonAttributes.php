<?php

namespace Context\Page\Batch;

use Context\Page\Base\Wizard;
use Behat\Mink\Element\Element;

/**
 * Edit common attributes page
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends Wizard
{
    protected $elements = array(
        'Available attributes button'     => array('css' => 'button:contains("Select attributes")'),
        'Available attributes add button' => array('css' => '.pimmultiselect a:contains("Select")'),
        'Available attributes form'       => array(
            'css' => '#pim_catalog_mass_edit_action_operation_attributesToDisplay'
        ),
    );

    /**
     * This method allows to fill a compound field by passing the label in reversed order separated
     * with whitespaces.
     *
     * Example:
     * We have a field "$" embedded inside a "Price" field
     * We can call fillField('$ Price', 26) to set the "$" value of parent field "Price"
     *
     * @param string  $labelContent
     * @param string  $value
     * @param Element $element
     */
    public function fillField($labelContent, $value, Element $element = null)
    {
        $parsedField = str_word_count($labelContent, 1, '€$');

        if (2 === count($parsedField)) {
            $subLabelContent = $parsedField[0];
            $labelContent    = $parsedField[1];
        }

        if ($element) {
            $label = $element->find('css', sprintf('label:contains("%s")', $labelContent));
        } else {
            $label = $this->find('css', sprintf('#pim_catalog_mass_edit_action label:contains("%s")', $labelContent));
        }

        if (null === $label) {
            throw new \InvalidArgumentException(sprintf('Impossible to find field %s', $field));
        }

        if ($label->hasAttribute('for')) {
            if (false === strpos($value, ',')) {
                $field = $this->find('css', sprintf('#%s', $label->getAttribute('for')));
                $field->setValue($value);
            } else {
                foreach (explode(',', $value) as $value) {
                    $field = $label->getParent()->find('css', 'select');
                    $field->selectOption(trim($value), true);
                }
            }
        } else {
            if (!$subLabelContent) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'The "%s" field is compound but the sub label was not provided',
                        $labelContent
                    )
                );
            }

            // it is a compound field, so let's expand the values
            $this->expand($label);

            $this->fillField($subLabelContent, $value, $label->getParent());
        }
    }

    private function expand($label)
    {
        if ($icon = $label->getParent()->find('css', '.icon-caret-right')) {
            $icon->click();
        }
    }
}
