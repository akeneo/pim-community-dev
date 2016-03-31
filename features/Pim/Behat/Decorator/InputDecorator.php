<?php

namespace Pim\Behat\Decorator;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Context\Spin\SpinCapableTrait;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InputDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    protected $selectors = [
        'Input label' => ['css' => '.field-container header label:contains("%s")'],
        'Text inputs' => ['css' => '.field-input input, .field-input textarea'],
    ];

    /**
     * @param string $label
     * @param bool   $copy
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    public function findField($label, $copy = false)
    {
        if (1 === preg_match('/ in (.{1,3})$/', $label)) {
            // Price in EUR
            list($label, $currency) = explode(' in ', $label);
            $fieldContainer = $this->findFieldContainer($label);

            return $this->findCompoundField($fieldContainer, $currency);
        }

        $subContainer = $this->spin(function () use ($label, $copy) {
            return $this->findFieldContainer($label)
                ->find('css', $copy ? '.copy-container .form-field' : '.form-field');
        });

        $field = $this->spin(function () use ($subContainer) {
            return $subContainer->find('css', $this->selectors['Text inputs']['css']);
        });

        return $field;
    }

    /**
     * Find field container
     *
     * @param string $label
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    public function findFieldContainer($label)
    {
        if (1 === preg_match('/ in (.{1,3})$/', $label)) {
            // Price in EUR
            $label = explode(' in ', $label)[0];
        }

        $selector = sprintf($this->selectors['Input label']['css'], $label);

        $labelNode = $this->spin(function () use ($label, $selector) {
            return $this->find('css', $selector);
        }, sprintf('Cannot find the input label "%s" (%s)', $label, $selector));

        $container = $this->spin(function () use ($labelNode) {
            return $labelNode->getParent()->getParent()->getParent();
        });

        $container->name = $label;

        return $container;
    }

    /**
     * Find a compound field
     *
     * @param NodeElement $fieldContainer
     * @param             $currency
     *
     * @throws ElementNotFoundException
     *
     * @return NodeElement
     */
    protected function findCompoundField($fieldContainer, $currency)
    {
        $input = $this->spin(function () use ($fieldContainer, $currency) {
            return $fieldContainer->find('css', sprintf('input[data-currency=%s]', $currency));
        }, sprintf(
            'Cannot find the compound field with currency %s in container %s',
            $currency,
            $fieldContainer->name
        ));

        return $input;
    }

    /**
     * @param string $inputLabel
     * @param string $currency
     *
     * @return NodeElement
     */
    public function findCurrencyInput($inputLabel, $currency)
    {
        $fieldContainer = $this->findFieldContainer($inputLabel);

        return $this->findCompoundField($fieldContainer, $currency);
    }
}
