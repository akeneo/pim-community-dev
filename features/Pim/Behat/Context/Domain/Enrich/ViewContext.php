<?php

namespace Pim\Behat\Context\Domain\Enrich;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Then;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use Pim\Behat\Context\PimContext;

/**
 * A context for managing custom views of pages
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ViewContext extends PimContext
{
    use SpinCapableTrait;

    /**
     * @param string $viewLabel
     *
     * @When /^I apply the "([^"]*)" view$/
     */
    public function iApplyTheView($viewLabel)
    {
        $viewSelector = $this->getCurrentPage()->getCurrentViewSelector();
        $viewSelector->showViewList($viewLabel);
        $viewSelector->applyView($viewLabel);
        $this->wait();
    }

    /**
     * @When /^I delete the view$/
     */
    public function iDeleteTheView()
    {
        $this->getCurrentPage()->getCurrentViewSelector()->deleteView();
        $this->wait();
    }

    /**
     * @param TableNode $table
     *
     * @return Then[]
     *
     * @When /^I create the view:$/
     */
    public function iCreateTheView(TableNode $table)
    {
        $this->getCurrentPage()->getCurrentViewSelector()->createView();

        return [
            new Step\Then('I fill in the following information in the popin:', $table),
            new Step\Then('I press the "OK" button')
        ];
    }

    /**
     * @When /^I update the view$/
     */
    public function iUpdateTheView()
    {
        $this->getCurrentPage()->getCurrentViewSelector()->updateView();
        $this->wait();
    }

    /**
     * @param string $not
     * @param string $viewLabel
     *
     * @Then /^I should( not)? see the "([^"]*)" view$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheView($not, $viewLabel)
    {
        $view = $this->getCurrentPage()->getCurrentViewList()->findView($viewLabel);

        if (('' !== $not && null !== $view) || ('' === $not && null === $view)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf(
                    'View "%s" should%s be available.',
                    $viewLabel,
                    $not
                )
            );
        }
    }
}
