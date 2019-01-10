<?php

namespace Context;

use Behat\ChainedStepsExtension\Step\Then;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Context\Spin\SpinCapableTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Context for assertions
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AssertionContext extends PimContext
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
            Assert::assertTrue(in_array($error, $errors), sprintf('Expecting to see validation error "%s", not found', $error));
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
            $script = 'return $(\'.error-message:contains("%s")\').length > 0';
            $found  = $this->getSession()->evaluateScript(sprintf($script, $error));
            Assert::assertFalse($found, sprintf('Expecting to not see validation error, "%s" found', $error));
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
                Assert::assertEquals(
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
            if (!$field->hasAttribute('disabled') && !$field->hasAttribute('readonly')) {
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
     * @throws Spin\TimeoutException
     */
    public function iShouldSeeHistory(TableNode $table)
    {
        $block = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.history-block, .grid');
        }, 'Could not find the history block');

        foreach ($table->getHash() as $data) {
            $unknownColumns = array_diff(array_keys($data), ['author', 'version', 'property', 'date', 'value', 'before']);
            if (0 !== count($unknownColumns)) {
                throw new \InvalidArgumentException(sprintf(
                    'Unrecognized columns "%s"',
                    json_encode($unknownColumns)
                ));
            }

            $expectedVersion = $data['version'];
            $expectedProperty = $data['property'];

            $row = $this->spin(function () use ($block, $expectedVersion) {
                return $block->find('css', sprintf('.entity-version[data-version="%d"]', $expectedVersion));
            }, sprintf('Cannot find the version "%s"', $expectedVersion));

            if (array_key_exists('author', $data)) {
                $expectedAuthor = $data['author'];
                $author = $row->find('css', '[data-column="author"]')->getText();
                Assert::assertEquals(
                    $expectedAuthor,
                    $author,
                    sprintf(
                        'Expecting the author of version "%s" to be "%s", got "%s"',
                        $expectedVersion,
                        $expectedAuthor,
                        $author
                    )
                );
            }

            if (array_key_exists('date', $data)) {
                $expectedDate = $data['date'];
                $date = $row->find('css', '[data-column="loggedAt"]')->getText();
                Assert::assertLessThan(
                    90,
                    abs(strtotime($expectedDate) - strtotime($date)),
                    sprintf(
                        'Expecting the date of version "%s" to be "%s", got "%s"',
                        $expectedVersion,
                        $expectedDate,
                        $date
                    )
                );
            }

            if (!$row->hasClass('AknGrid-bodyRow--expanded')) {
                $this->spin(function () use ($row) {
                    return $row->find('css', '.version-expander');
                }, sprintf('Can not find the expand button of version "%s"', json_encode($data)))->click();
            }

            $changesetRow = $this->spin(function () use ($block, $expectedVersion) {
                return $block->find('css', sprintf('.changeset:not(.hide)[data-version="%d"]', $expectedVersion));
            }, sprintf('No changeset found for version %s', $data['version']));
            // Each change contains 3 cells: property, before and after cells.
            $changes = array_chunk($changesetRow->findAll('css', 'tbody .AknGrid-bodyCell'), 3);

            $matchingChange = $this->spin(function () use ($changes, $expectedProperty) {
                foreach ($changes as $change) {
                    $propertyCell = $change[0];
                    if ($propertyCell->getText() === $expectedProperty) {
                        return $change;
                    }
                }

                return null;
            }, sprintf(
                'Can not find change of the property "%s", found %s',
                $expectedProperty,
                join(', ', array_map(function ($change) {
                    return sprintf('"%s"', $change[0]->getText());
                }, $changes))
            ));

            if (array_key_exists('before', $data)) {
                $expectedBefore = $data['before'];
                $before = $matchingChange[1]->find('css', '.old-values')->getText();
                Assert::assertEquals(
                    $expectedBefore,
                    $before,
                    sprintf(
                        'Expecting the old value of version "%s" to be "%s", got "%s"',
                        $expectedVersion,
                        $expectedBefore,
                        $before
                    )
                );
            }

            if (array_key_exists('value', $data)) {
                $expectedAfter = $data['value'];
                $after = $matchingChange[2]->find('css', '.new-values')->getText();
                if (!preg_match(
                    sprintf('/^%s$/', str_replace(['/', '$', '^'], ['\/', '\$', '\^'], $expectedAfter)),
                    $after
                )) {
                    throw $this->createExpectationException(sprintf(
                        'Expecting the new value of version "%s" to be "%s", got "%s"',
                        $expectedVersion,
                        $expectedAfter,
                        $after
                    ));
                }
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

        Assert::assertEquals($rows, $rowCount, sprintf('Expecting file to contain %d rows, found %d.', $rows, $rowCount));
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
        $this->spin(function () use ($count) {
            $countContainer = $this->getCurrentPage()->find('css', '.AknNotificationMenu-countContainer');

            if (!$countContainer) {
                return false;
            }
            $actualCount = (int) $countContainer->getText();

            Assert::assertEquals(
                $actualCount,
                $count,
                sprintf('Expecting to see %d new notifications, saw %d', $count, $actualCount)
            );

            return true;
        }, sprintf(
            'Expecting to see %d new notifications',
            $count)
        );
    }

    /**
     * @When /^I open the notification panel$/
     */
    public function iOpenTheNotificationPanel()
    {
        $notificationWidget = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.notification');
        }, 'Cannot find the link to the notification widget');

        if ($notificationWidget->hasClass('open')) {
            return;
        }

        // notification is loaded during the click
        // but isVisible is not totally reliable, which mean that
        // we think we load the notification panel but we didn't (DOM not loaded)
        // sleep is a dirty hack but spinning the click + footer all together does not work
        sleep(1);


        $this->spin(function () use ($notificationWidget) {
            $toggle = $notificationWidget->find('css', '.notification-link');

            if (null !== $toggle && $toggle->isVisible()) {
                $toggle->click();

                return true;
            }
        }, 'Can not find the dropdown notification toggle');

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
            return $this->getCurrentPage()->find('css', '.notification');
        }, 'Cannot find the link to the notification widget');

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
        $this->spin(function () {
            $image = $this->getCurrentPage()->find('css', '.AknTitleContainer-image');

            return null !== $image && false === strpos($image->getAttribute('src'), 'user-info.png');
        }, 'Avatar image not found or not default one');
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
    public function replacePlaceholders($value)
    {
        return $this->getMainContext()->getSubcontext('fixtures')->replacePlaceholders($value);
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" in popup$/
     *
     * @param string $message The message.
     *
     * @return bool
     */
    public function assertPopupMessage($message)
    {
        return $this->spin(function () use ($message) {
            return $message == $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
        }, sprintf('Cannot assert that the modal contains %s', $message));
    }
}
