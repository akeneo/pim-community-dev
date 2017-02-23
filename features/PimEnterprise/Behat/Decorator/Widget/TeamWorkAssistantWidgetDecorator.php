<?php

namespace PimEnterprise\Behat\Decorator\Widget;

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * team work assistant widget decorator
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class TeamWorkAssistantWidgetDecorator extends ElementDecorator
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
     * Get the completeness from team work assistant widget
     * Returns ['todo' => (string), 'in_progress' => (string), 'done' => (string)]
     *
     * @return array
     */
    public function getCompleteness()
    {
        return [
            'todo'        => $this->getCompletenessNumber('todo'),
            'in_progress' => $this->getCompletenessNumber('in-progress'),
            'done'        => $this->getCompletenessNumber('done')
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
            return $this->find('css', sprintf('.team-work-assistant-completeness-%s', $needle));
        }, sprintf('"%s" box not found in completeness.', $needle));

        return $box->getText();
    }

    /**
     * Click on the section with the given $sectionName on this widget.
     *
     * @param string $sectionName
     */
    public function clickOnSection($sectionName)
    {
        $box = $this->spin(function () use ($sectionName) {
            return $this->find('css', sprintf('.team-work-assistant-completeness-%s', $sectionName));
        }, sprintf('"%s" box not found in completeness.', $sectionName));

        $box->getParent()->mouseOver();

        $link = $this->spin(function () use ($box) {
            return $box->getParent()->find('css', 'a');
        }, sprintf('Link not found in box "%s" of the team work assistant widget.', $sectionName));

        $link->click();
    }
}
