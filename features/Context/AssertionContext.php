<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * Context for assertions
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssertionContext extends RawMinkContext
{
    /**
     * @param string $text
     *
     * @Then /^I should see a tooltip "([^"]*)"$/
     */
    public function iShouldSeeATooltip($text)
    {
        if (!$this->getCurrentPage()->findTooltip($text)) {
            throw $this->createExpectationException(sprintf('No tooltip containing "%s" were found.', $text));
        }
    }

    /**
     * @param string $error
     *
     * @Then /^I should see validation error "([^"]*)"$/
     */
    public function iShouldSeeValidationError($error)
    {
        $errors = $this->getCurrentPage()->getValidationErrors();
        assertTrue(in_array($error, $errors), sprintf('Expecting to see validation error "%s", not found', $error));
    }

    /**
     * @return Page
     */
    private function getCurrentPage()
    {
        return $this->getMainContext()->getSubcontext('navigation')->getCurrentPage();
    }
}
