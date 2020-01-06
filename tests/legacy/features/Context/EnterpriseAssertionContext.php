<?php

namespace Context;

use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Context\AssertionContext as BaseAssertionContext;

/**
 * Assertion context
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseAssertionContext extends BaseAssertionContext
{
    /**
     * @param $version
     *
     * @Then /^the version (\d+) should be marked as published$/
     *
     * @throws ExpectationException
     */
    public function versionShouldBeMarkedAsPublished($version)
    {
        $row = $this->spin(function () use ($version) {
            return $this->getCurrentPage()->find('css', '.history-block tr[data-version="' . $version . '"]');
        }, sprintf('Cannot find history row for version "%d"', $version));

        if (!$row->find('css', '.label-published')) {
            throw $this->createExpectationException(
                sprintf('Expecting to see version %d marked as published, but is not', $version)
            );
        }
    }

    /**
     * @Then /^I should see that "([^"]+)" characters are remaining$/
     *
     * @param int $expectedNumber
     *
     * @throws ExpectationException
     */
    public function iShouldSeeThatCharactersAreRemaining($expectedNumber)
    {
        $modalBodyContent     = $this->getCurrentPage()->find('css', '.modal-body');
        $remainingCharContent = $modalBodyContent->find('css', '.remaining-chars');
        $remainingChar        = $remainingCharContent->getText();

        if ($remainingChar !== $expectedNumber) {
            throw $this->createExpectationException(
                sprintf('Expecting to see "%s" remaining chars but got "%s".', $expectedNumber, $remainingChar)
            );
        }
    }

    /**
     * @Given /^I should not be able to send the comment$/
     *
     * @throws ExpectationException
     */
    public function iShouldNotBeAbleToSendTheComment()
    {
        $disabledOkBtn = $this->getCurrentPage()->find('css', '.modal .ok[disabled=disabled]');

        if (null === $disabledOkBtn) {
            throw $this->createExpectationException('Expecting to see the Send button disabled, it was not.');
        }
    }

    /**
     * @Then /^I should see a project validation error "([^"]*)"$/
     *
     * @param string $expectedErrorMessage
     *
     * @throws ExpectationException
     */
    public function iShouldSeeAProjectValidationError($expectedErrorMessage)
    {
        $projectModal = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.control-group');
        }, 'Impossible to find the modal project form');

        $errors = $this->spin(function () use ($projectModal) {
            $errors = $projectModal->findAll('css', '.AknFieldContainer-validationError');

            return count($errors) > 0 ? $errors : false;
        }, 'Impossible to find validation errors');

        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = $error->getText();
        }

        if (!in_array($expectedErrorMessage, $errorMessages)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see the validation error "%s", but not found.',
                    $expectedErrorMessage
                )
            );
        }
    }
}
