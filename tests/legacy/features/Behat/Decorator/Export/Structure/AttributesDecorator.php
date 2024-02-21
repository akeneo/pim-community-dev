<?php

namespace Pim\Behat\Decorator\Export\Structure;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\Common\AttributeSelectorDecorator;
use Pim\Behat\Decorator\ElementDecorator;

/**
 * Decorate the add attributes element
 */
class AttributesDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Select the given attributes
     *
     * @param array $attributes
     */
    public function selectAttributes(array $attributes)
    {
        $this->open();
        $modal = $this->getModal();

        $modal->clear();
        $modal->selectAttributes($attributes);
        $modal->close();
    }

    /**
     * Open the select attributes modal
     */
    public function open()
    {
        $editButton = $this->spin(function () {
            return $this->find('css', 'button.edit');
        }, 'Cannot find the open button');

        $editButton->click();
    }

    /**
     * Get the select attribute modal
     */
    protected function getModal()
    {
        $modal = $this->spin(function () {
            return $this->getBody()->find('css', '.modal');
        }, 'Cannot find the select filter modal');

        return $this->decorate($modal, [AttributeSelectorDecorator::class]);
    }
}
