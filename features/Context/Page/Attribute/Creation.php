<?php

namespace Context\Page\Attribute;

use Behat\Mink\Element\NodeElement;
use Context\Page\Base\Form;

/**
 * Attribute creation page
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Creation extends Form
{
    /**
     * @var string
     */
    protected $path = '#/configuration/attribute/create';

    /**
     * {@inheritdoc}
     */
    public function __construct($session, $pageFactory, $parameters = [])
    {
        parent::__construct($session, $pageFactory, $parameters);

        $this->elements = array_merge(
            $this->elements,
            [
                'attribute_option_table' => ['css' => '.attribute-option-grid table'],
                'attribute_options'      => ['css' => '.attribute-option-grid tbody tr'],
                'add_option_button'      => ['css' => '.attribute-option-grid .option-add'],
                'new_option'             => ['css' => '.in-edition']
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function findField($name)
    {
        $field = parent::findField($name);
        if (!$field) {
            $field = $this->getElement('attribute_option_table')->find('css', sprintf('th:contains("%s")', $name));
        }

        return $field;
    }

    /**
     * Add an attribute option
     *
     * @param string $code
     * @param array  $labels
     */
    public function addOption($code, array $labels = [])
    {
        $this->createOption();
        $this->fillNewOption($code, $labels);
        $this->saveNewOption();
    }

    public function createOption()
    {
        $this->spin(function () {
            $loadingWrapper = $this->find('css', '.loading-mask');

            return (null === $loadingWrapper || !$loadingWrapper->isVisible());
        }, 'Loading mask is still visible');

        $table = $this->getElement('attribute_option_table');

        $this->spin(function () use ($table) {
            $this->getElement('add_option_button')->click();

            return $table->find('css', '.attribute_option_code');
        }, 'Cannot add a new attribute option');
    }

    /**
     * @param string $name
     * @param array  $labels
     */
    public function fillNewOption($name, $labels = [])
    {
        $row = $this->getElement('new_option');

        $codeField = $this->spin(function () use ($row) {
            return $row->find('css', '.attribute_option_code');
        }, 'Unable to find the attribute option code field');

        $codeField->setValue($name);

        foreach ($labels as $locale => $label) {
            $labelField = $row->find('css', sprintf('.attribute-option-value[data-locale="%s"]', $locale));
            $labelField->setValue($label);
        }
    }

    public function saveNewOption()
    {
        $this->spin(function () {
            $this->getElement('new_option')
                ->find('css', '.update-row')
                ->click();

            return true;
        }, 'Cannot save new option.');
    }

    /**
     * Edit and cancel edition on an attribute option
     *
     * @param string $name
     * @param string $newValue
     */
    public function editOptionAndCancel($name, $newValue)
    {
        $row = $this->spin(function () use ($name) {
            return $this->getOptionElement($name);
        }, sprintf('Cannot find option row "%s"', $name));

        $editButton = $this->spin(function () use ($row) {
            return $row->find('css', '.edit-row');
        }, sprintf('Cannot find edit button for row "%s"', $name));

        $editButton->click();
        $row->find('css', '.attribute-option-value:first-child')->setValue($newValue);
        $row->find('css', '.show-row')->click();
    }

    /**
     * Edit an attribute option
     *
     * @param string $code
     * @param array  $labels
     */
    public function editOption($code, array $labels = [])
    {
        $row = $this->getOptionElement($code);

        $row->find('css', '.edit-row')->click();

        foreach ($labels as $locale => $value) {
            $row->find('css', sprintf('.attribute-option-value[data-locale="%s"]', $locale))->setValue($value);
        }

        $row->find('css', '.update-row')->click();
    }

    /**
     * Count the number of attribute options
     *
     * @return int
     */
    public function countOptions()
    {
        $this->spin(function () {
            $table = $this->find('css', $this->elements['attribute_option_table']['css']);

            if (null !== $table) {
                $this->spin(function () {
                    $row = $this->find('css', $this->elements['new_option']['css']);

                    return null === $row;
                }, 'Cannot find new option button in attribute option table');
            }

            return true;
        }, 'Attribute options are not visible');

        return count($this->findAll('css', $this->elements['attribute_options']['css']));
    }

    /**
     * Count the number of attribute options
     *
     * @return int
     */
    public function countOrderableOptions()
    {
        return count($this->findAll('css', $this->elements['attribute_option_table']['css'].':not(.ui-sortable-disabled) .handle'));
    }

    /**
     * Remove a specific option name
     *
     * @param string $optionName
     *
     * @throws \InvalidArgumentException
     */
    public function removeOption($optionName)
    {
        $optionRow = $this->spin(function () use ($optionName) {
            return $this->getOptionElement($optionName);
        }, 'Cannot find delete option button.');

        $deleteBtn = $optionRow->find('css', '.delete-row');

        $this->spin(function () use ($deleteBtn) {
            $deleteBtn->click();

            return true;
        }, 'Cannot click on delete option button.');
    }

    /**
     * Checks that the attribute options are in the expected order in the grid.
     *
     * @param array $expectedOrder
     */
    public function checkOptionsOrder(array $expectedOrder)
    {
        $this->spin(function () use ($expectedOrder) {
            $rows = $this->getOptionsElement();
            $actualOrder = array_map(function ($row) {
                return $row->find('css', '.option-code')->getText();
            }, $rows);

            return $actualOrder === $expectedOrder;
        }, 'Attribute options are not ordered as expected.');
    }

    /**
     * Get option elements
     *
     * @return array
     */
    protected function getOptionsElement()
    {
        return $this->findAll('css', $this->elements['attribute_options']['css']);
    }

    /**
     * Get a specific option row from the option code
     *
     * @param string $optionName
     *
     * @throws \InvalidArgumentException
     *
     * @return NodeElement
     */
    protected function getOptionElement($optionName)
    {
        foreach ($this->getOptionsElement() as $optionRow) {
            if ($optionRow->find('css', '.option-code') &&
                $optionRow->find('css', '.option-code')->getText() === $optionName
            ) {
                return $optionRow;
            }
        }

        throw new \InvalidArgumentException(sprintf('Option %s was not found', $optionName));
    }

    /**
     * Select the attribute type in the modal
     *
     * @param $name
     *
     */
    public function selectAttributeType($name)
    {
        $this->spin(function () use ($name) {
            $fields = $this->findAll('css', '.attribute-choice');
            foreach ($fields as $field) {
                if (strtolower(trim($field->getText())) === strtolower($name)) {
                    return $field;
                }
            }

            return null;
        }, sprintf('Cannot find attribute type "%s"', $name))->click();
    }

    /**
     * Find a validation tooltip containing a text
     *
     * @param string $text
     *
     * @return NodeElement
     */
    public function findValidationTooltip(string $text)
    {
        return $this->spin(function () use ($text) {
            return $this->find(
                'css',
                sprintf(
                    '.error-message:contains("%s"), .validation-tooltip[data-original-title="%s"]',
                    $text, $text
                )
            );
        }, sprintf('Cannot find error message "%s" in validation tooltip', $text));
    }
}
