<?php

namespace PimEnterprise\Behat\Decorator\Widget;

use Behat\Mink\Element\NodeElement;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Decorator\ElementDecorator;
use Pim\Behat\Decorator\Field\Select2Decorator;

/**
 * teamwork assistant widget decorator
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class TeamworkAssistantWidgetDecorator extends ElementDecorator
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
     * Get the completeness from teamwork assistant widget
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
            return $this->find('css', sprintf('.teamwork-assistant-completeness-%s', $needle));
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
        $this->getLinkFromSection($sectionName)->click();
    }

    /**
     * Get link to product grid from a teamwork assistant widget section or null if it doesn't exists.
     *
     * @param string $sectionName
     *
     * @return NodeElement|null
     */
    public function getLinkFromSection($sectionName)
    {
        $sectionOrder = [
            'todo' => 0,
            'in-progress' => 1,
            'done' => 2,
        ];

        return $this->spin(function () use ($sectionOrder, $sectionName) {
            $link = $this->findAll('css', 'a[class*="project-datagrid-link"]');
            if (count($link) === 0) {
                return false;
            }

            return $link[$sectionOrder[$sectionName]];
        }, sprintf('"%s" box not found in completeness.', $sectionName));
    }

    public function selectContributor(string $contributorName)
    {
        $this->openWidgetDropdown('.contributor-selector');

        $contributorLabel = $this->spin(function () use ($contributorName) {
            $label = $this->getBody()->find('css', sprintf('.contributor-label:contains("%s")', $contributorName));
            if ($label === null) {
                return false;
            }

            return $label;
        }, 'contributor label not found');


        $contributorLabel->click();
    }

    public function selectProject(string $projectLabel)
    {
        $this->openWidgetDropdown('.project-selector');

        $projectLabel = $this->spin(function () use ($projectLabel) {
            $label = $this->getBody()->find('css', sprintf('.project-label:contains("%s")', $projectLabel));
            if ($label === null) {
                return false;
            }

            return $label;
        }, 'project label not found');


        $projectLabel->click();
    }

    public function getChoicesFromProjectsSelector()
    {
        $this->openWidgetDropdown('.project-selector');

        $projectsLabels = $this->spin(function () {
            $labelElements = $this->getBody()->findAll('css', '.project-label');
            if (count($labelElements) === 0) {
                return false;
            }

            $labels = [];
            foreach ($labelElements as $label) {
                $labels[] = $label->getText();
            }

            return $labels;
        }, 'projects labels not found');

        $this->getBody()->find('css', '[data-testid="backdrop"]')->click();

        return $projectsLabels;
    }

    private function openWidgetDropdown(string $selector)
    {
        $dropdownLabel = $this->spin(function () use ($selector) {
            $dropdownLabel = $this->find('css', $selector);
            if ($dropdownLabel === null) {
                return false;
            }

            return $dropdownLabel;
        }, 'selector not found');

        $dropdownLabel->click();
    }
}
