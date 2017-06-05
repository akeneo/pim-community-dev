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
                'attribute_option_table' => ['css' => '#attribute-option-grid table'],
                'attribute_options'      => ['css' => '#attribute-option-grid tbody tr'],
                'add_option_button'      => ['css' => '#attribute-option-grid .option-add'],
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
     * @param string $name
     * @param array  $labels
     */
    public function addOption($name, array $labels = [])
    {
        if (null === $this->getElement('attribute_option_table')->find('css', '.attribute_option_code')) {
            $this->createOption();
        }

        $this->fillLastOption($name, $labels);
        $this->saveLastOption();

        return $this->getElement('attribute_option_table')->find(
            'css',
            sprintf('.option-code:contains("%s")', $name)
        );
    }

    public function createOption()
    {
        $this->spin(function () {
            $this->getElement('add_option_button')->click();

            return true;
        }, 'Cannot add a new attribute option');

        $this->spin(function () {
            return $this->getElement('attribute_option_table')->find('css', '.attribute_option_code');
        }, 'The click on new option has not added a new line.');
    }

    /**
     * @param string $name
     * @param array  $labels
     */
    public function fillLastOption($name, $labels = [])
    {
        $row = $this->getLastOption();

        $this->spin(function () use ($row) {
            return $row->find('css', '.attribute_option_code');
        }, 'Unable to find the attribute option code field')->setValue($name);

        foreach ($labels as $locale => $label) {
            $this->spin(function () use ($row, $label, $locale) {
                return $row->find('css', sprintf('.attribute-option-value[data-locale="%s"]', $locale));
            }, sprintf('Unable fo find attribute option with locale "%s"', $locale))->setValue($label);
        }
    }

    public function saveLastOption()
    {
        $this->getLastOption()->find('css', '.update-row')->click();
    }

    /**
     * @return NodeElement
     */
    protected function getLastOption()
    {
        $rows = $this->getOptionsElement();

        return end($rows);
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
        }, 'Cannot find option row');

        $row->find('css', '.edit-row')->click();
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
        return count($this->findAll('css', '#attribute-option-grid table:not(.ui-sortable-disabled) .handle'));
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
        }, 'Cannot find option row');
        $deleteBtn = $optionRow->find('css', '.delete-row');

        if ($deleteBtn === null) {
            throw new \InvalidArgumentException(
                sprintf('Remove bouton not found or disabled for %s option', $optionName)
            );
        }

        $deleteBtn->click();
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
     * @return \Behat\Mink\Element\NodeElement
     */
    protected function getOptionElement($optionName)
    {
        foreach ($this->getOptionsElement() as $optionRow) {
            if ((
                    $optionRow->find('css', '.attribute_option_code') &&
                    $optionRow->find('css', '.attribute_option_code')->getValue() === $optionName
                ) ||
                (
                    $optionRow->find('css', '.option-code') &&
                    $optionRow->find('css', '.option-code')->getText() === $optionName
                )
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
                if (trim($field->getText()) === $name) {
                    return $field;
                }
            }

            return null;
        }, sprintf('Cannot find attribute type "%s"', $name))->click();
    }
}
