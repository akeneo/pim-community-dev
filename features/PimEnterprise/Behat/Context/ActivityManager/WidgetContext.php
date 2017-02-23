<?php

namespace PimEnterprise\Behat\Context\TeamWorkAssistant;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Context\PimContext;
use PimEnterprise\Behat\Decorator\Widget\TeamWorkAssistantWidgetDecorator;

class WidgetContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @Then /^I should not see the (project) selector$/
     * @Then /^I should not see the (contributor) selector$/
     */
    public function iShouldNotSeeTheSelector($selector)
    {
        $getSelectorMethod = sprintf('get%sSelector', ucfirst($selector));
        try {
            $this->getTeamWorkAssistantWidget()->$getSelectorMethod();
            throw new ExpectationException(
                sprintf('%s selector is visible but must not.', $selector),
                $this->getSession()
            );
        } catch (TimeoutException $e) {
            return true;
        }
    }

    /**
     * @Then /^I should see the team work assistant widget$/
     */
    public function iShouldSeeTheTeamWorkAssistantWidget()
    {
        $this->getTeamWorkAssistantWidget();
    }

    /**
     * @When /^I select "([^"]*)" project$/
     *
     * @param string $projectLabel
     */
    public function iSelectProject($projectLabel)
    {
        $this->getTeamWorkAssistantWidget()->getProjectSelector()->setValue($projectLabel);
    }

    /**
     * @When /^I select "([^"]*)" contributor$/
     *
     * @param string $contributorName
     */
    public function iSelectContributor($contributorName)
    {
        $this->getTeamWorkAssistantWidget()->getContributorSelector()->setValue($contributorName);
    }

    /**
     * @Then /^I should see the following team work assistant completeness:$/
     *
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function iShouldSeeTheFollowingTeamWorkAssistantCompleteness(TableNode $table)
    {
        $completeness = $this->getTeamWorkAssistantWidget()->getCompleteness();
        foreach ($table->getHash() as $expectedData) {
            foreach ($expectedData as $field => $expectedValue) {
                if ($completeness[$field] !== $expectedValue) {
                    throw new ExpectationException(
                        sprintf(
                            'Expected "%s:%s" for completeness and "%s" found.',
                            $field, $expectedValue, $completeness[$field]
                        ),
                        $this->getSession()
                    );
                }
            }
        }
    }

    /**
     * @Then /^I should( not)? see the "([^"]*)" project in the widget$/
     *
     * @param string $not
     * @param string $projectName
     *
     * @throws \UnexpectedValueException
     */
    public function iShouldSeeTheProject($not, $projectName)
    {
        $values = $this->getTeamWorkAssistantWidget()->getProjectSelector()->getAvailableValues();
        $found = false;

        foreach ($values as $value) {
            if (strpos($value, $projectName) !== false) {
                $found = true;
            }
        }

        if ($not && $found) {
            throw new \UnexpectedValueException(
                sprintf('Project "%s" should not be displayed.', $projectName)
            );
        } elseif (!$not && !$found) {
            throw new \UnexpectedValueException(
                sprintf('Project "%s" should be displayed.', $projectName)
            );
        }
    }

    /**
     * @When /^I click on the "([^"]*)" section of the team work assistant widget$/
     *
     * @param string $sectionName
     */
    public function iClickOnTheSectionOfTheTeamWorkAssistantWidget($sectionName)
    {
        $this->getTeamWorkAssistantWidget()->clickOnSection($sectionName);
    }

    /**
     * Get the decorated team work assistant widget
     *
     * @return TeamWorkAssistantWidgetDecorator
     */
    protected function getTeamWorkAssistantWidget()
    {
        return $this->getCurrentPage()->getTeamWorkAssistantWidget();
    }
}
