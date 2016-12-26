<?php

namespace PimEnterprise\Behat\Context\ActivityManager;

use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Pim\Behat\Context\PimContext;
use PimEnterprise\Behat\Decorator\Widget\ActivityManagerWidgetDecorator;

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
            $this->getActivityManagerWidget()->$getSelectorMethod();
            throw new ExpectationException(
                sprintf('%s selector is visible but must not.', $selector),
                $this->getSession()
            );
        } catch (TimeoutException $e) {
            return true;
        }
    }

    /**
     * @Then /^I should see the Activity Manager widget$/
     */
    public function iShouldSeeTheActivityManagerWidget()
    {
        $this->getActivityManagerWidget();
    }

    /**
     * @When /^I select "([^"]*)" project$/
     *
     * @param string $projectLabel
     */
    public function iSelectProject($projectLabel)
    {
        $this->getActivityManagerWidget()->getProjectSelector()->setValue($projectLabel);
    }

    /**
     * @When /^I select "([^"]*)" contributor$/
     *
     * @param string $contributorName
     */
    public function iSelectContributor($contributorName)
    {
        $this->getActivityManagerWidget()->getContributorSelector()->setValue($contributorName);
    }

    /**
     * @Then /^I should see the following activity manager completeness:$/
     *
     * @param TableNode $table
     *
     * @throws \Exception
     */
    public function iShouldSeeTheFollowingActivityManagerCompleteness(TableNode $table)
    {
        $completeness = $this->getActivityManagerWidget()->getCompleteness();
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
     * Get the decorated Activity Manager widget
     *
     * @return ActivityManagerWidgetDecorator
     */
    protected function getActivityManagerWidget()
    {
        return $this->getCurrentPage()->getActivityManagerWidget();
    }
}
