<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Behat\Context;

use Akeneo\ActivityManager\Behat\Context;
use Akeneo\ActivityManager\Behat\ContextInterface;
use Behat\Gherkin\Node\TableNode;
use Context\Spin\SpinCapableTrait;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Webmozart\Assert\Assert;

/**
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectContext extends Context implements ContextInterface
{
    use SpinCapableTrait;

    /**
     * @Then /^I should be on the project show page/
     */
    public function iShouldBeOnTheProjectPage()
    {
        Assert::true($this->getCurrentPage()->isOpen());
    }

    /**
     * @Given /^I open the view selector$/
     */
    public function iOpenTheViewSelector()
    {
        $this->getCurrentPage()->getViewSelector()->click();
    }

    /**
     * @Then /^the project "([^"]*)" has the following properties:$/
     */
    public function projectHasProperties($label, TableNode $properties)
    {
        $project = $this->getContainer()
            ->get('akeneo_activity_manager.project.repository')
            ->findOneBy(['label' => $label]);

        $accessor = PropertyAccess::createPropertyAccessor();

        foreach ($properties->getRows() as $property) {
            list($propertyName, $expectedValue) = $property;
            if ($expectedValue !== $actualValue = $accessor->getValue($project, $propertyName)) {
                throw new \DomainException(
                    sprintf(
                        'Given value does not match the expected value, "%s" expected, "%s" given, property: "%s"',
                        $expectedValue,
                        $actualValue,
                        $propertyName
                    )
                );
            }
        }
    }

    /**
     * @Then /^I click on Create view button in the dropdown$/
     */
    public function iClickOnCreateViewButton()
    {
        $dropdown = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.btn-group:contains("Create todo")');
        }, 'Dropdown button not found');

        $dropdownToggle = $this->spin(function () use ($dropdown) {
            return $dropdown->find('css', '.dropdown-toggle');
        }, 'Dropdown toggle button not found');
        $dropdownToggle->click();

        $dropdownMenu = $dropdownToggle->getParent()->find('css', '.dropdown-menu');

        $createViewBtn = $this->spin(function () use ($dropdownMenu) {
            return $dropdownMenu->find('css', 'li:contains("Create view") [data-action="prompt-creation"]');
        }, 'Item "Create view" of dropdown button not found');
        $createViewBtn->click();
    }
}
