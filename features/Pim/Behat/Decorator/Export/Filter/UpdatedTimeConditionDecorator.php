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
    private $elements = [
        'updated_since_strategy' => [
            'css'        => '.select2-container',
            'decorators' => ['Pim\Behat\Decorator\Field\Select2Decorator']
        ],
        'updated_since_date' => [
            'css'        => '.exported-since-date-wrapper input',
            'decorators' => ['Pim\Behat\Decorator\Field\DatepickerDecorator']
        ],
        'updated_since_period' => [
            'css' => '.exported-since-period-wrapper input',
        ],
    ];

    /**
     * Set the value of the filter
     * 
     * @param string $expectedOperatorValue
     * @param string $expectedFilterValue
     */
    public function setValue($expectedOperatorValue, $expectedFilterValue)
    {
        $operator = $this->getElement('updated_since_strategy');
        $operator->setValue($expectedOperatorValue);

        $filterValueElement = $this->getFilterValueElement($operator);
        $filterValueElement->setValue($expectedFilterValue);
    }
    
    /**
     * Check if the operator and the value of the filter are valid
     * 
     * @param string $expectedOperatorValue
     * @param string $expectedFilterValue
     * 
     * @throws \Exception
     */
    public function validate($expectedOperatorValue, $expectedFilterValue)
    {
        $operator = $this->getElement('updated_since_strategy');
        $operatorOptionValue = $operator->getOptionLabel();
        if ($expectedOperatorValue !== $operatorOptionValue) {
            throw new \Exception(
                sprintf(
                    'The value of operator does not contain "%s" but "%s"', 
                    $expectedOperatorValue, 
                    $operatorOptionValue
                )
            );
        }

        usleep(200);

        $filterValue = $this->getFilterValueElement($operator)->getValue();
        if ($expectedFilterValue !== $filterValue) {
            throw new \Exception(
                sprintf('The value of filter does not contain "%s" but "%s"', $filterValue, $expectedFilterValue)
            );
        }
    }
    
    public function hasVisibleFilterValue($field)
    {
        $filter = $this->getElement($field);

        if ($filter->isVisible()) {
            throw new \Exception('The date input for the updated condition time should not be visible');
        }
    }

    /**
     * Return a decorated Element
     * 
     * @param string $name
     * 
     * @return ElementDecorator|NodeElement
     */
    private function getElement($name)
    {
        $element = $this->spin(function() use ($name) {
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
    private function getFilterValueElement($operator)
    {
        return $this->getElement(sprintf('updated_%s', $operator->getValue()));
    }
}
