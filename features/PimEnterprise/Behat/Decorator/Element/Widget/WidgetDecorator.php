<?php

namespace Akeneo\ActivityManager\Behat\Decorator\Element\Widget;

use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * Activity manager widget decorator
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class WidgetDecorator extends ElementDecorator
{
    use SpinCapableTrait;

    /**
     * Return the decorator project select2
     *
     * @return Select2Decorator
     */
    public function getProjectSelector()
    {
        $selector = $this->spin(function () {
            return $this->find('css', '.project-selector-select2');
        }, 'Project selector not found.');

        return $this->decorate($selector, ['Pim\Behat\Decorator\Field\Select2Decorator']);
    }

    /**
     * Return the decorator contributor select2
     *
     * @return Select2Decorator
     */
    public function getContributorSelector()
    {
        $selector = $this->spin(function () {
            return $this->find('css', '.contributor-selector-select2');
        }, 'Contributor selector not found.');

        return $this->decorate($selector, ['Pim\Behat\Decorator\Field\Select2Decorator']);
    }

    /**
     * Get the completeness from Activity Manager widget
     * Returns ['todo' => (string), 'in_progress' => (string), 'done' => (string)]
     *
     * @return array
     */
    public function getCompleteness()
    {
        return [
            'todo' => $this->getCompletenessNumber('todo'),
            'in_progress' => $this->getCompletenessNumber('in-progress'),
            'done' => $this->getCompletenessNumber('done')
        ];
    }


    /**
     * @param string $needle
     *
     * @return string
     */
    public function getCompletenessNumber($needle)
    {
        $box = $this->spin(function () use ($needle) {
            return $this->find('css', sprintf('.activity-manager-completeness-%s', $needle));
        }, sprintf('""%s" box not found in completeness.', $needle));

        return $box->getText();
    }
}
