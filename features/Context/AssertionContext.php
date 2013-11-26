<?php

namespace Context;

use Behat\Behat\Context\Step\Then;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Gherkin\Node\TableNode;

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
     * @param string $expectedTitle
     *
     * @Then /^I should see the title "([^"]*)"$/
     */
    public function iShouldSeeTheTitle($expectedTitle)
    {
        $actualTitle = $this->getCurrentPage()->getHeadTitle();
        if (trim($actualTitle) !== trim($expectedTitle)) {
            throw $this->createExpectationException(
                sprintf('Incorrect title. Expected "%s", found "%s"', $expectedTitle, $actualTitle)
            );
        }
    }

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
     * @param string $fields
     *
     * @Then /^I should see the (.*) fields?$/
     */
    public function iShouldSeeTheFields($fields)
    {
        $fields = $this->getMainContext()->listToArray($fields);
        foreach ($fields as $field) {
            try {
                if (!$this->getCurrentPage()->findField($field)) {
                    throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $field));
                }
            } catch (ElementNotFoundException $e) {
                throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $field));
            }
        }
    }

    /**
     * @param string $fields
     *
     * @Then /^I should not see the (.*) fields?$/
     */
    public function iShouldNotSeeTheFields($fields)
    {
        $fields = $this->getMainContext()->listToArray($fields);

        foreach ($fields as $field) {
            try {
                if ($this->getCurrentPage()->findField($field)) {
                    throw $this->createExpectationException(sprintf('Not expecting to see field "%s"', $field));
                }
            } catch (ElementNotFoundException $e) {
            }
        }
    }

    /**
     * @param string $fields
     *
     * @Given /^the fields? (.*) should be disabled$/
     */
    public function theFieldsShouldBeDisabled($fields)
    {
        $fields = $this->getMainContext()->listToArray($fields);
        foreach ($fields as $fieldName) {
            $field = $this->getCurrentPage()->findField($fieldName);
            if (!$field) {
                throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $fieldName));

                return;
            }
            if (!$field->hasAttribute('disabled')) {
                throw $this->createExpectationException(sprintf('Expecting field "%s" to be disabled.', $fieldName));
            }
        }
    }

    /**
     * @param string $text
     *
     * @Then /^I should see (?:a )?flash message "([^"]*)"$/
     */
    public function iShouldSeeFlashMessage($text)
    {
        if (!$this->getCurrentPage()->findFlashMessage($text)) {
            throw $this->createExpectationException(sprintf('No flash messages containing "%s" were found.', $text));
        }
    }

    /**
     * @param TableNode $tableNode
     *
     * @Then /^I should see a confirm dialog with the following content:$/
     */
    public function iShouldSeeAConfirmDialog(TableNode $tableNode)
    {
        $tableHash = $tableNode->getHash();

        if (isset($tableHash['title'])) {
            $expectedTitle = $tableHash['title'];
            $title = $this->getCurrentPage()->getConfirmDialogTitle();

            if ($expectedTitle !== $title) {
                $this->createExpectationException(
                    sprintf('Expecting confirm dialog title "%s", saw "%s"', $expectedTitle, $title)
                );
            }
        }

        if (isset($tableHash['content'])) {
            $expectedContent = $tableHash['content'];
            $content = $this->getCurrentPage()->getConfirmDialogContent();

            if ($expectedContent !== $content) {
                $this->createExpectationException(
                    sprintf('Expecting confirm dialog content "%s", saw "%s"', $expectedContent, $content)
                );
            }
        }
    }

    /**
     * @param string $link
     *
     * @Then /^I should not see the "([^"]*)" link$/
     */
    public function iShouldNotSeeTheLink($link)
    {
        if ($this->getCurrentPage()->findLink($link)) {
            throw $this->createExpectationException(sprintf('Link %s should not be displayed', $link));
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^I should see history:$/
     */
    public function iShouldSeeHistory(TableNode $table)
    {
        $updates = array();
        $rows = $this->getCurrentPage()->getHistoryRows();
        foreach ($rows as $row) {
            $action  = $row->find('css', 'td.string-cell')->getHtml();
            $version = $row->find('css', 'td.integer-cell')->getHtml();

            $items = $row->find('css', 'td.string-cell ul')->findAll('css', 'li');

            foreach ($items as $item) {
                $property = trim($item->find('css', 'b')->getHtml());
                $property = str_replace(':', '', $property);
                $value = $item->getHtml();
                $value = trim(preg_replace('/(<b>.*<\/b>)|(<s>.*<\/s>)/', '', $value));
                $updates[] = sprintf('%s-%s-%s-%s', $action, $version, $property, $value);
            }
        }

        $expectedUpdates = $table->getHash();
        foreach ($expectedUpdates as $row) {
            $expectedUpdate = sprintf('%s-%s-%s-%s', $row['action'], $row['version'], $row['property'], $row['value']);
            if (!in_array($expectedUpdate, $updates)) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see history row %s, not found', implode(', ', $row))
                );
            }
        }
    }

    /**
     * @param string $file
     *
     * @Given /^file "([^"]*)" should exist$/
     */
    public function fileShouldExist($file)
    {
        if (!file_exists($file)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $file));
        }

        unlink($file);
    }

    /**
     * @param string  $fileName
     * @param integer $rows
     *
     * @Given /^file "([^"]*)" should contain (\d+) rows$/
     */
    public function fileShouldContainRows($fileName, $rows)
    {
        if (!file_exists($fileName)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $fileName));
        }

        $file = fopen($fileName, 'rb');
        $rowCount = 0;
        while (fgets($file) !== false) {
            $rowCount++;
        }
        fclose($file);

        assertEquals($rows, $rowCount, sprintf('Expecting file to contain %d rows, found %d.', $rows, $rowCount));
    }

    /**
     * @param string    $entity
     * @param TableNode $table
     *
     * @return Then[]
     * @Then /^the following (.*) codes should not be available:$/
     */
    public function theFollowingCodesShouldNotBeAvailable($entity, TableNode $table)
    {
        $steps = array();

        foreach ($table->getHash() as $item) {
            $steps[] = new Then(sprintf('I change the Code to "%s"', $item['code']));
            $steps[] = new Then(sprintf('I save the %s', $entity));
            $steps[] = new Then('I should see validation error "This code is not available."');
        }

        return $steps;
    }

    /**
     * @return Page
     */
    private function getCurrentPage()
    {
        return $this->getMainContext()->getSubcontext('navigation')->getCurrentPage();
    }

    /**
     * @param string $message
     *
     * @return ExpectationException
     */
    private function createExpectationException($message)
    {
        return $this->getMainContext()->createExpectationException($message);
    }
}
