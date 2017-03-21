<?php

namespace Context;

use Behat\Behat\Context\Step\Then;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
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
        $text = str_replace('\\"', '"', $text);
        $text = strip_tags($text);
        $this->spin(function () use ($text) {
            $this->assertSession()->pageTextContains($text);

            return true;
        }, sprintf('Cannot find the text "%s"', $text));
    }

    /**
     * Checks, that page does not contain specified text.
     *
     * @Then /^I should not see the text "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function assertPageNotContainsText($text)
    {
        //Remove unecessary escaped antislashes
        $text = str_replace('\\"', '"', $text);
        $text = strip_tags($text);
        $this->spin(function () use ($text) {
            $this->assertSession()->pageTextNotContains($text);

            return true;
        }, sprintf('The text "%s" has been found in page', $text));
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
        $this->spin(function () use ($expectedTitle) {
            return trim($this->getCurrentPage()->getHeadTitle()) === trim($expectedTitle);
        }, sprintf(
            'Incorrect title. Expected "%s", found "%s"',
            $expectedTitle,
            $this->getCurrentPage()->getHeadTitle())
        );
    }

    /**
     * @param string $error
     *
     * @Then /^I should see(?: a)? validation (?:error|tooltip) "([^"]*)"$/
     */
    public function iShouldSeeValidationError($error)
    {
        if ($this->getSession()->getDriver() instanceof Selenium2Driver) {
            $protectedError = addslashes($error);
            $script = 'return $(\'.validation-tooltip[data-original-title="%s"]\').length > 0';
            $found  = $this->getSession()->evaluateScript(sprintf($script, $protectedError));
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
     * @param string $message
     *
     * @Then /^I should see the tooltip "([^"]*)"$/
     */
    public function iShouldSeeTheTooltip($message)
    {
        $this->spin(function () use ($message) {
            $tooltipMessages = $this->getCurrentPage()->getTooltipMessages();

            return in_array($message, $tooltipMessages);
        }, sprintf('Expecting to see tooltip "%s", not found', $message));
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
        $priceLabelField = $this->spin(function () use ($field) {
            return $this->getCurrentPage()->findField($field);
        }, sprintf('Expecting to see the price field "%s".', $field));

        $currencies = explode(',', $currencies);
        $currencies = array_map('trim', $currencies);
        $priceField = $priceLabelField->getParent()->getParent();

        foreach ($currencies as $currency) {
            $this->spin(function () use ($priceField, $currency, $field) {
                return $priceField->find('css', sprintf('input[data-currency="%s"]', $currency));
            }, sprintf('Expecting to see the currency "%s" on price field "%s".', $currency, $field));
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
     * @deprecated This function was disabled because it generates too many failing tests. Warning, some tests are
     * always using it, and it checks nothing at all.
     *
     * @param $text
     *
     * @Then /^I should see the flash message "(.*)"$/
     */
    public function iShouldSeeTheFlashMessage($text)
    {
        return;

        $this->spin(function () use ($text) {
            $flashes = $this->getCurrentPage()->findAll('css', '.flash-messages-holder > div');
            foreach ($flashes as $flash) {
                if (false !== strpos($flash->getText(), $text)) {
                    return true;
                }
            }

            return null;
        }, sprintf('Can not find flash message with text "%s"', $text));
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

        $valuePattern = '/(.)*<strong>%s:<\/strong>\s*%s\s*(.)*/';

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
        }, 'Could not find the history block');

        foreach ($table->getHash() as $data) {
            $row = $this->spin(function () use ($block, $data) {
                return $block->find('css', 'tr[data-version="' . $data['version'] . '"]');
            }, sprintf('Cannot find the row %s', $data['version']));

            if (!$row) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see history row for version %s, not found', $data['version'])
                );
            }
            if (!$row->hasClass('expanded')) {
                $row->find('css', '.version-expander')->click();
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

            $changesetRows = $this->spin(function () use ($row) {
                return $row->getParent()->findAll('css', '.changeset:not(.hide) tbody tr');
            }, sprintf('No changeset found for version %s', $data['version']));

            $matchingRow = null;
            $parsedTexts = [];
            foreach ($changesetRows as $row) {
                $innerHtml = $row->find('css', 'td:first-of-type')->getHtml();

                $parsedText = trim(preg_replace('/(<[^>]+>)+/', ' ', $innerHtml));
                $parsedText = preg_replace('/\s+/', ' ', $parsedText);
                $parsedTexts[] = $parsedText;

                if ($parsedText === $data['property']) {
                    $matchingRow = $row;
                    break;
                }
            }

            if (!$matchingRow) {
                throw $this->createExpectationException(
                    sprintf('No row found for property %s, found %s', $data['property'], implode(', ', $parsedTexts))
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
                sprintf('/^%s$/', str_replace(['/', '$', '^'], ['\/', '\$', '\^'], $newValue)),
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
     *
     * @Given /^file "([^"]*)" should not exist$/
     *
     * @throws ExpectationException
     */
    public function fileShouldNotExist($fileName)
    {
        $fileName = $this->replacePlaceholders($fileName);
        if (file_exists($fileName)) {
            throw $this->createExpectationException(sprintf('File %s exists.', $fileName));
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
     * @param int $count
     *
     * @Then /^I should have (\d+) new notifications?$/
     */
    public function iShouldHaveNewNotification($count)
    {
        $actualCount = (int) $this->getCurrentPage()->find('css', '.AknBell-countContainer')->getText();

        assertEquals(
            $actualCount,
            $count,
            sprintf('Expecting to see %d new notifications, saw %d', $count, $actualCount)
        );
    }

    /**
     * @When /^I open the notification panel$/
     */
    public function iOpenTheNotificationPanel()
    {
        $notificationWidget = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '#header-notification-widget');
        }, 'Cannot find "#header-notification-widget" notification panel');

        if ($notificationWidget->hasClass('open')) {
            return;
        }

        $notificationWidget->find('css', '.dropdown-toggle')->click();

        // Wait for the footer of the notification panel dropdown to be loaded
        $this->spin(function () {
            $footer  = $this->getCurrentPage()->find('css', '.AknNotificationList-footer');
            $content = trim($footer->getText());

            return !empty($content);
        }, 'Notification panel content should not be empty');
    }

    /**
     * @When /^I click on the notification "([^"]+)"$/
     */
    public function iClickOnTheNotification($message)
    {
        $this->iOpenTheNotificationPanel();
        $page = $this->getCurrentPage();
        $selector = sprintf('.AknNotification-link:contains("%s")', $message);

        $link = $this->spin(function () use ($page, $selector) {
            return $page->find('css', $selector);
        }, sprintf('Cannot find "%s" element', $selector));

        $link->click();
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
        $this->iOpenTheNotificationPanel();

        $notificationWidget = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '#header-notification-widget');
        }, 'Cannot find "#header-notification-widget" notification widget');

        $icons = [
            'success' => 'icon-ok',
            'warning' => 'icon-warning-sign',
            'error'   => 'icon-remove',
            'add'     => 'icon-plus',
        ];

        foreach ($table->getHash() as $data) {
            $notifications = $notificationWidget->findAll('css', '.AknNotification-link');

            $matchingNotification = null;

            foreach ($notifications as $notification) {
                if (null === $matchingNotification && false !== strpos($notification->getText(), $data['message'])) {
                    $matchingNotification = $notification;
                }
            }

            if (null === $matchingNotification) {
                $notificationTexts = array_map(function ($notification) {
                    return sprintf("'%s'", $notification->getText());
                }, $notifications);

                throw $this->createExpectationException(
                    sprintf(
                        "Notification '%s' not found.\nAvailable notifications: %s",
                        $data['message'],
                        implode(', ', $notificationTexts)
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

            if (!$matchingNotification->find('css', sprintf('i.%s', $icons[$data['type']]))) {
                throw $this->createExpectationException(
                    sprintf(
                        'Expecting the type of notification "%s" to be "%s"',
                        $data['message'],
                        $data['type']
                    )
                );
            }

            if (isset($data['comment']) && '' !== $data['comment']) {
                $commentNode = $matchingNotification->find('css', '.AknNotification-comment');

                if (!$commentNode) {
                    throw $this->createExpectationException(
                        sprintf(
                            'Expecting notification "%s" to have a comment.',
                            $data['message']
                        )
                    );
                }

                if ($data['comment'] !== $commentNode->getText()) {
                    throw $this->createExpectationException(
                        sprintf(
                            'Expecting notification "%s" to have the comment "%s", got "%s"',
                            $data['message'],
                            $data['comment'],
                            $commentNode->getText()
                        )
                    );
                }
            }
        }
    }

    /**
     * Checks that avatar was not the default one
     *
     * @Then /^I should not see the default avatar$/
     */
    public function iShouldNotSeeDefaultAvatar()
    {
        $this->assertSession()->elementAttributeNotContains('css', '.AknTitleContainer-avatar', 'src', 'user-info.png');
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
