<?php

namespace Pim\Behat\Decorator\Export\Filter;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Export Builder - Decorator for the filter "Updated time condition"
 */
class UpdatedTimeConditionDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /** @var array */
    protected $elements = [
        'updated since strategy' => [
            'css'        => '.select2-container',
            'decorators' => ['Pim\Behat\Decorator\Field\Select2Decorator'],
        ],
        'updated since date' => [
            'css'        => '.exported-since-date-wrapper input',
            'decorators' => ['Pim\Behat\Decorator\Field\DatepickerDecorator'],
        ],
        'updated since n days' => [
            'css' => '.exported-since-n-days-wrapper input',
        ],
    ];
    
    /**
     * Get the value of the operator
     *
     * @return string
     */
    public function getOperator()
    {
        return $this->getElement('updated since strategy')->getValues()[0];
    }

    /**
     * Set the value of the filter
     *
     * @param string $operator
     * @param string $value
     */
    public function setValue($operator, $value)
    {
        $operatorElement = $this->getElement('updated since strategy');
        $operatorElement->setValue($operator);

        $filterValueElement = $this->getFilterValueElement($operatorElement);
        $filterValueElement->setValue($value);
    }

    /**
     * Get the value of the filter
     * 
     * @return string
     */
    public function getValue()
    {
        $operator = $this->getElement('updated since strategy');
        
        return $this->getFilterValueElement($operator)->getValue();
    }

    /**
     * Check the visibility of the filter
     *
     * @param string $field
     *
     * @return bool
     */
    public function checkValueElementVisibility($field)
    {
        $filter = $this->getElement($field);

        return $filter->isVisible();
    }

    /**
     * Return a decorated Element
     *
     * @param string $name
     *
     * @return ElementDecorator|NodeElement
     */
    protected function getElement($name)
    {
        $element = $this->spin(function () use ($name) {
            return $this->find('css', $this->elements[$name]['css']);
        }, sprintf('Impossible to find the element %s', $name));

        if (isset($this->elements[$name]['decorators'])) {
            $element = $this->decorate($element, $this->elements[$name]['decorators']);
        }

        return $element;
    }

    /**
     * Get the element used to managed the value of the filter
     *
     * @param mixed $operator
     *
     * @return NodeElement
     */
    protected function getFilterValueElement($operator)
    {
        $formattedCode = str_replace('_', ' ',  $operator->getCodes()[0]);

        return $this->getElement(sprintf('updated %s', $formattedCode));
    }
}
