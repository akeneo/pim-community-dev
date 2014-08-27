<?php

namespace Context;

use Behat\Behat\Context\Step;
use Context\WebUser as BaseWebUser;

/**
 * Overrided context
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseWebUser extends BaseWebUser
{
    /**
     * Override parent
     */
    public function iChooseTheOperation($operation)
    {
        $this->getNavigationContext()->currentPage = $this
            ->getPage('Batch Operation')
            ->addStep('Publish products', 'Batch Publish')
            ->addStep('Unpublish products', 'Batch Unpublish')
            ->chooseOperation($operation)
            ->next();

        $this->wait();
    }

    /**
     * @Given /^I should not see a single form input$/
     */
    public function iShouldNotSeeASingleFormInput()
    {
        new Step\Given('I should not see an "input" element');
    }
    /**
    * @Then /^the view mode field (.*) should contain "([^"]*)"$/
     */
    public function theProductViewModeFieldValueShouldBe($fieldName, $expected = '')
    {
        $field = $this->getCurrentPage()->findField($fieldName);
        $actual = trim($field->getHtml());

        if ($expected != $actual) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected product view mode field "%s" to contain "%s", but got "%s".',
                    $fieldName,
                    $expected,
                    $actual
                )
            );
        }
    }
}
