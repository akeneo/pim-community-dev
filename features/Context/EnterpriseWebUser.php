<?php

namespace Context;

use Behat\Behat\Context\Step;
use Behat\Mink\Driver\Selenium2Driver;
use Context\WebUser as BaseWebUser;

/**
 * Overrided context
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseWebUser extends BaseWebUser
{
    const MASS_PUBLISH_LOG_PATH = 'app/logs/mass_action.log';

    /**
     * Override parent
     *
     * {@inheritdoc}
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
     * @param string $fieldName
     * @param string $expected
     *
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

    /**
     * @Then /^I should see the smart attribute tooltip$/
     */
    public function iShouldSeeTheTooltip()
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $script = 'return $(\'.icon-code-fork[data-async-content]\').length > 0';
            $found = $this->getSession()->evaluateScript($script);
            if ($found) {
                return;
            }
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see smart attribute tooltip'
                )
            );
        }
    }

    /**
     * @Given /^I wait for the mass publish to finish$/
     */
    public function iWaitForTheMassPublishToFinish()
    {
        $logFilePath = __DIR__ . '/../../' . self::MASS_PUBLISH_LOG_PATH;
        if (!file_exists($logFilePath)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see the mass publish log file to the path : "%s". None found.',
                    $logFilePath
                )
            );
        }

        $i = 10;
        $massPublishFinished = false;
        while (!$massPublishFinished && $i > 0) {
            sleep(2);
            $logContent = file_get_contents($logFilePath);
            if (false !== strpos($logContent, 'Associations have been published.')) {
                $massPublishFinished = true;
            }
            $i--;
        }

        if (!$massPublishFinished) {
            throw $this->createExpectationException(
                sprintf(
                    'Mass publish is not yet finished after 20s running or an error occurred. Log file content : "%s"',
                    $logContent
                )
            );
        }
    }
}
