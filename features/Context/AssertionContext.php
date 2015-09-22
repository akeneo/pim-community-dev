<?php

namespace Context;

use Behat\Behat\Context\Step\Then;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ResponseTextException;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\Spin\SpinCapableTrait;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Context for assertions
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssertionContext extends RawMinkContext
{
    use SpinCapableTrait;

    /**
     * Checks, that page contains specified text.
     *
     * @Then /^(?:|I )should see the text "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageContainsText($text)
    {
        //Remove unecessary escaped antislashes
        $text = str_replace('\\', '', $text);
        $this->spin(function () use ($text) {
            $this->assertSession()->pageTextContains($text);

            return true;
        });
    }

    /**
     * Checks, that page does not contain specified text.
     *
     * @Then /^(?:|I )should not see the text "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageNotContainsText($text)
    {
        $this->spin(function () use ($text) {
            $this->assertSession()->pageTextNotContains($text);

            return true;
        }, 5);
    }

    /**
     * @param string $expectedTitle
     *
     * @Then /^I should see the title "([^"]*)"$/
     *
     * @throws ExpectationException
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
     * @param string $error
     *
     * @Then /^I should see(?: a)? validation (?:error|tooltip) "([^"]*)"$/
     */
    public function iShouldSeeValidationError($error)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $script = 'return $(\'.validation-tooltip[data-original-title="%s"]\').length > 0';
            $found  = $this->getSession()->evaluateScript(sprintf($script, $error));
            if ($found) {
                return;
            }
        }

        if (!$this->getCurrentPage()->findValidationTooltip($error)) {
            $this->getMainContext()->wait();
            $errors = $this->getCurrentPage()->getValidationErrors();
            assertTrue(in_array($error, $errors), sprintf('Expecting to see validation error "%s", not found', $error));
        }
    }

    /**
     * @param string $error
     *
     * @Then /^I should not see(?: a)? validation (?:error|tooltip) "([^"]*)"$/
     */
    public function iShouldNotSeeValidationError($error)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $script = 'return $(\'.validation-tooltip[data-original-title="%s"]\').length > 0';
            $found  = $this->getSession()->evaluateScript(sprintf($script, $error));
            assertFalse($found, sprintf('Expecting to not see validation error, "%s" found', $error));
        }
    }

    /**
     * @param string $tab
     *
     * @Then /^the "([^"]*)" tab should (?:be red|have errors)$/
     */
    public function theTabShouldHaveErrors($tab)
    {
        $links = $this->getCurrentPage()->getTabs();

        foreach ($links as $link) {
            if ($link->getText() != $tab) {
                $link->click();
                break;
            }
        }
        $this->getMainContext()->wait();

        foreach ($links as $link) {
            if ($link->getText() == $tab) {
                assertEquals(
                    $link->getAttribute('class'),
                    'error',
                    sprintf('Expecting tab %s to have class "error", not found.', $tab)
                );
                break;
            }
        }
    }

    /**
     * @param string $fields
     *
     * @Then /^I should see the (.*) fields?$/
     *
     * @throws ExpectationException
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
     * @param string $currencies
     * @param string $field
     *
     * @Then /^I should see "(.+)" currencies on the (.*) price field$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeCurrenciesOnThePriceField($currencies, $field)
    {
        if (null === $priceLabelField = $this->getCurrentPage()->findField($field)) {
            throw $this->createExpectationException(sprintf('Expecting to see the price field "%s".', $field));
        }
        $currencies = explode(',', $currencies);
        $currencies = array_map('trim', $currencies);
        $priceField = $priceLabelField->getParent();

        foreach ($currencies as $currency) {
            if (null === $priceField->find('css', sprintf('.controls input[value="%s"]', $currency))) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see the currency "%s" on price field "%s".', $currency, $field)
                );
            }
        }
    }

    /**
     * @param string $fields
     *
     * @Then /^I should not see the (.*) fields?$/
     *
     * @throws ExpectationException
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
            } catch (\Exception $e) {
                if ($e instanceof ExpectationException) {
                    throw $e;
                }
            }
        }
    }

    /**
     * @param string $fields
     *
     * @Given /^the fields? (.*) should be disabled$/
     *
     * @throws ExpectationException
     */
    public function theFieldsShouldBeDisabled($fields)
    {
        $fields = $this->getMainContext()->listToArray($fields);
        foreach ($fields as $fieldName) {
            $field = $this->getCurrentPage()->findField($fieldName);
            if (!$field) {
                throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $fieldName));
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
     *
     * @throws ExpectationException
     *
     * @return bool
     */
    public function iShouldSeeFlashMessage($text)
    {
        // TODO Flash messages tests temporarily disabled because unstable on CI
        return true;
//        $this->getMainContext()->wait(10000, '$(".flash-messages-holder").length > 0');
//        if (!$this->getCurrentPage()->findFlashMessage($text)) {
//            throw $this->createExpectationException(sprintf('No flash messages containing "%s" were found.', $text));
//        }
    }

    /**
     * @param TableNode $tableNode
     *
     * @Then /^I should see a dialog with the following content:$/
     * @Then /^I should see a confirm dialog with the following content:$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeAConfirmDialog(TableNode $tableNode)
    {
        $tableHash = $tableNode->getHash();

        if (isset($tableHash['title'])) {
            $expectedTitle = $tableHash['title'];
            $title         = $this->getCurrentPage()->getConfirmDialogTitle();

            if ($expectedTitle !== $title) {
                throw $this->createExpectationException(
                    sprintf('Expecting confirm dialog title "%s", saw "%s"', $expectedTitle, $title)
                );
            }
        }

        if (isset($tableHash['content'])) {
            $expectedContent = $tableHash['content'];
            $content         = $this->getCurrentPage()->getConfirmDialogContent();

            if ($expectedContent !== $content) {
                throw $this->createExpectationException(
                    sprintf('Expecting confirm dialog content "%s", saw "%s"', $expectedContent, $content)
                );
            }
        }
    }

    /**
     * @param string $link
     *
     * @Then /^I should not see the "([^"]*)" link$/
     *
     * @throws ExpectationException
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
     *
     * @throws ExpectationException
     */
    public function iShouldSeeHistory(TableNode $table)
    {
        if ($this->getCurrentPage()->find('css', '.panel-container')) {
            $this->iShouldSeeHistoryInPanel($table);

            return;
        }

        $updates = [];
        $rows    = $this->getCurrentPage()->getHistoryRows();
        foreach ($rows as $row) {
            $version = (int) $row->find('css', 'td.number-cell')->getHtml();
            $author  = $row->findAll('css', 'td.string-cell');
            if (count($author) > 4) {
                $author = $row->findAll('css', 'td.string-cell')[1]->getHtml();
            } else {
                $author = $row->findAll('css', 'td.string-cell')[0]->getHtml();
            }
            $data = $row->findAll('css', 'td>ul');
            $data = end($data);
            $data = preg_replace('/\s+|\n+|\r+/m', ' ', $data->getHtml());

            $updates[] = [
                'version' => $version,
                'data'    => $data,
                'author'  => $author,
            ];
        }

        $valuePattern = '/(.)*<b>%s:<\/b>\s*%s\s*(.)*/';

        $expectedUpdates = $table->getHash();
        foreach ($expectedUpdates as $data) {
            if (!array_key_exists('author', $data)) {
                $data['author'] = '';
            }
            $expectedPattern = sprintf(
                $valuePattern,
                $data['property'],
                $data['value'],
                $data['author']
            );

            $found = false;
            foreach ($updates as $update) {
                if ('' === $data['author']) {
                    $update['author'] = '';
                }
                if ((int) $data['version'] === $update['version']) {
                    if (preg_match($expectedPattern, $update['data'])
                        && $data['author'] === $update['author']) {
                        $found = true;
                        break;
                    }
                }
            }

            if (!$found) {
                throw $this->createExpectationException(
                    sprintf(
                        'Expecting to see history update %d - %s - %s - %s, not found',
                        $data['version'],
                        $data['author'],
                        $data['property'],
                        $data['value']
                    )
                );
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^I should see history in panel:$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeHistoryInPanel(TableNode $table)
    {
        $block = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.history-block');
        });

        foreach ($table->getHash() as $data) {
            $row = $this->spin(function () use ($block, $data) {
                return $block->find('css', 'tr[data-version="' . $data['version'] . '"]');
            });

            if (!$row) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see history row for version %s, not found', $data['version'])
                );
            }
            if (!$row->hasClass('expanded')) {
                $row->click();
            }
            if (isset($data['author'])) {
                $author = $row->find('css', 'td.author')->getText();
                assertEquals(
                    $data['author'],
                    $author,
                    sprintf(
                        'Expecting the author of version %s to be %s, got %s',
                        $data['version'],
                        $data['author'],
                        $author
                    )
                );
            }

            $changeset = $row->getParent()->find('css', 'tr.changeset:not(.hide) tbody');
            if (!$changeset) {
                throw $this->createExpectationException(
                    sprintf('No changeset found for version %s', $data['version'])
                );
            }
            $changesetRows = $changeset->findAll('css', 'tr');
            if (!$changesetRows) {
                throw $this->createExpectationException(
                    sprintf('No changeset rows found for version %s', $data['version'])
                );
            }

            $matchingRow = null;
            $parsedText = '';
            foreach ($changesetRows as $row) {
                $innerHtml = $row->find('css', 'td:first-of-type')->getHtml();

                $parsedText = trim(preg_replace('/(<[^>]+>)+/', ' ', $innerHtml));
                $parsedText = preg_replace('/\s+/', ' ', $parsedText);

                if ($parsedText === $data['property']) {
                    $matchingRow = $row;
                    break;
                }
            }

            if (!$matchingRow) {
                throw $this->createExpectationException(
                    sprintf('No row found for property %s, found %s', $data['property'], $parsedText)
                );
            }

            $newValue = isset($data['value']) ? $data['value'] : $data['after'];
            $oldValue = isset($data['before']) ? $data['before'] : null;

            if ($matchingRow->find('css', 'td:nth-of-type(2)')->getText() !== $oldValue && $oldValue) {
                throw $this->createExpectationException(
                    sprintf('Wrong old value in row %s, expected %s', $data['property'], $newValue)
                );
            }

            if (!preg_match(
                sprintf('/^%s$/', $newValue),
                $actual = $matchingRow->find('css', 'td:last-of-type')->getText()
            )) {
                throw $this->createExpectationException(
                    sprintf(
                        'Wrong new value in row %s, expected %s, got %s',
                        $data['property'],
                        $newValue,
                        $actual
                    )
                );
            }
        }
    }

    /**
     * @param string $fileName
     *
     * @Given /^file "([^"]*)" should exist$/
     *
     * @throws ExpectationException
     */
    public function fileShouldExist($fileName)
    {
        $fileName = $this->replacePlaceholders($fileName);
        if (!file_exists($fileName)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $fileName));
        }
    }

    /**
     * @param string $fileName
     * @param int    $rows
     *
     * @Given /^file "([^"]*)" should contain (\d+) rows$/
     *
     * @throws ExpectationException
     */
    public function fileShouldContainRows($fileName, $rows)
    {
        $fileName = $this->replacePlaceholders($fileName);
        if (!file_exists($fileName)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $fileName));
        }

        $file     = fopen($fileName, 'rb');
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
     *
     * @Then /^the following (.*) codes should not be available:$/
     */
    public function theFollowingCodesShouldNotBeAvailable($entity, TableNode $table)
    {
        $steps = [];

        foreach ($table->getHash() as $item) {
            $steps[] = new Then(sprintf('I change the Code to "%s"', $item['code']));
            $steps[] = new Then(sprintf('I save the %s', $entity));
            $steps[] = new Then('I should see validation error "This code is not available"');
        }

        return $steps;
    }

    /**
     * @param TableNode $table
     *
     * @return Then[]
     *
     * @Then /^the following pages should have the following titles:$/
     */
    public function theFollowingPagesShouldHaveTheFollowingTitles($table)
    {
        $steps = [];

        foreach ($table->getHash() as $item) {
            $steps[] = new Then(sprintf('I am on the %s page', $item['page']));
            $steps[] = new Then(sprintf('I should see the title "%s"', $item['title']));
        }

        return $steps;
    }

    /**
     * @param string    $field
     * @param TableNode $table
     *
     * @Then /^the scopable "([^"]*)" field should have the following colors:$/
     */
    public function theScopableFieldShouldHaveTheFollowingColors($field, TableNode $table)
    {
        $element = $this->getCurrentPage()->find('css', sprintf('label:contains("%s")', $field))->getParent();
        $colors  = $this->getMainContext()->getContainer()->getParameter('pim_enrich.colors');
        foreach ($table->getHash() as $item) {
            $style = $element->find('css', sprintf('label[title="%s"]', $item['scope']))->getAttribute('style');
            assertGreaterThanOrEqual(
                1,
                strpos($style, $colors[$item['background']]),
                sprintf(
                    'Expecting the background of the %s %s field to be %s',
                    $item['scope'],
                    $field,
                    $item['background']
                )
            );
            assertGreaterThanOrEqual(
                1,
                strpos($style, $item['font']),
                sprintf(
                    'Expecting the font of the %s %s field to be %s',
                    $item['scope'],
                    $field,
                    $item['font']
                )
            );
        }
    }

    /**
     * @param int $count
     *
     * @Then /^I should have (\d+) new notification$/
     */
    public function iShouldHaveNewNotification($count)
    {
        $actualCount = $this->getCurrentPage()->find('css', '#header-notification-widget .indicator .badge')->getText();
        assertEquals(
            $actualCount,
            $count,
            sprintf('Expecting to see %d new notifications, saw %d', $count, $actualCount)
        );
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I should see notifications?:$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeNotifications(TableNode $table)
    {
        $element = $this->getCurrentPage()->find('css', '#header-notification-widget');
        $element->find('css', '.dropdown-toggle')->click();
        $this->getMainContext()->wait();

        $icons = [
            'success' => 'icon-ok',
            'warning' => 'icon-warning-sign',
            'error'   => 'icon-remove',
        ];

        foreach ($table->getHash() as $data) {
            $notification = $element->find('css', sprintf('.dropdown-menu li>a:contains("%s")', $data['message']));

            if (!$notification) {
                throw $this->createExpectationException(
                    sprintf(
                        'Expecting to see notification "%s", not found.',
                        $data['message']
                    )
                );
            }

            if (!isset($icons[$data['type']])) {
                throw $this->createExpectationException(
                    sprintf(
                        'Unknown notification type "%s". Known types are %s.',
                        $data['type'],
                        implode(', ', array_keys($icons))
                    )
                );
            }

            if (!$notification->find('css', sprintf('i.%s', $icons[$data['type']]))) {
                throw $this->createExpectationException(
                    sprintf(
                        'Expecting the type of notification "%s" to be "%s"',
                        $data['message'],
                        $data['type']
                    )
                );
            }
        }
    }

    /**
     * @param string $attribute
     *
     * @throws ExpectationException
     *
     * @return bool
     *
     * @Then /^I should see that (.*) is inherited from variant group attribute$/
     */
    public function iShouldSeeThatAttributeIsInheritedFromVariantGroup($attribute)
    {
        $footer = $this->getCurrentPage()->findFieldFooter($attribute);
        $error = $footer->find('css', '*:contains("Updated by variant group")');

        if (!$error) {
            throw $this->createExpectationException(
                'Affected by a variant group error was not found'
            );
        }
    }

    /**
     * @param string $attribute
     *
     * @throws ExpectationException
     *
     * @return bool
     *
     * @Then /^I should see that (.*) is not inherited from variant group attribute$/
     */
    public function iShouldSeeThatAttributeIsNotInheritedFromVariantGroup($attribute)
    {
        $footer = $this->getCurrentPage()->findFieldFooter($attribute);
        $error = $footer->find('css', '*:contains("Updated by variant group")');

        if ($error) {
            throw $this->createExpectationException(
                'Affected by a variant group error was found'
            );
        }
    }

    /**
     * @param $fieldName
     * @param $string
     *
     * @throws ExpectationException
     *
     * @return bool
     *
     *
     * @Then /^the field "([^"]*)" should have the following options:$/
     */
    public function theFieldShouldHaveTheFollowingOptions($fieldName, PyStringNode $string)
    {
        $field = $this->getCurrentPage()->findField($fieldName);
        $id    = $field->getAttribute('id');

        if ('select' === $field->getTagName()) {
            $options = $field->findAll('css', 'option');
        } elseif ('input' === $field->getTagName() && 0 === strpos($id, 's2id_')) {
            $options = $field->getParent()->getParent()->findAll('css', 'option');
        } else {
            throw $this->createExpectationException(
                sprintf('"%s" field is not a select field, can\'t have options.', $fieldName)
            );
        }

        $availableOptions = [];

        foreach ($options as $option) {
            $optionValue = trim($option->getText());

            if ($optionValue) {
                $availableOptions[] = $optionValue;
            }
        }

        if (count(array_intersect($string->getLines(), $availableOptions)) === count($string->getLines())) {
            return true;
        }

        throw $this->createExpectationException(
            sprintf(
                '"%s" field have these options (%s), but expected following options (%s).',
                $fieldName,
                implode(', ', $availableOptions),
                implode(', ', $string->getLines())
            )
        );
    }

    /**
     * @param PyStringNode $text
     *
     * @throws ResponseTextException
     * @throws \Exception
     *
     * @Then /^I should see the sequential edit progression:$/
     */
    public function iShouldSeeTheSequentialEditProgression(PyStringNode $text)
    {
        $this->getCurrentPage()->waitForProgressionBar();

        $this->spin(function () use ($text) {
            $this->assertSession()->pageTextContains((string) $text);

            return true;
        });
    }

    /**
     * Checks that avatar was not the default one
     *
     * @Then /^I should not see the default avatar$/
     */
    public function iShouldNotSeeDefaultAvatar()
    {
        $this->assertSession()->elementAttributeNotContains('css', '.customer-info img', 'src', 'user-info.png');
    }

    /**
     * @return Page
     */
    protected function getCurrentPage()
    {
        return $this->getMainContext()->getSubcontext('navigation')->getCurrentPage();
    }

    /**
     * @param string $message
     *
     * @return ExpectationException
     */
    protected function createExpectationException($message)
    {
        return $this->getMainContext()->createExpectationException($message);
    }

    /**
     * @param string $value
     *
     * @return string
     */
    protected function replacePlaceholders($value)
    {
        return $this->getMainContext()->getSubcontext('fixtures')->replacePlaceholders($value);
    }
}
