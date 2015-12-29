<?php

namespace Context;

use Behat\Behat\Context\Step;
use Behat\Behat\Context\Step\Then;
use Behat\Behat\Exception\BehaviorException;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\Spin\SpinCapableTrait;
use Pim\Bundle\CatalogBundle\Model\Product;
use Pim\Bundle\EnrichBundle\Mailer\MailRecorder;
use Pim\Bundle\EnrichBundle\MassEditAction\Operation\BatchableOperationInterface;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Context of the website
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebUser extends RawMinkContext
{
    use SpinCapableTrait;

    /* -------------------- Page-related methods -------------------- */


    /**
     * @param string $name
     *
     * @return Page
     */
    public function getPage($name)
    {
        return $this->getNavigationContext()->getPage($name);
    }

    /**
     * @param string $entity
     *
     * @Given /^I create a new ([^"]*)$/
     */
    public function iCreateANew($entity)
    {
        $entity = implode('', array_map('ucfirst', explode(' ', $entity)));
        $this->getPage(sprintf('%s index', $entity))->clickCreationLink();
        $this->getNavigationContext()->currentPage = sprintf('%s creation', $entity);
        $this->wait();
    }

    /**
     * @param string $type
     *
     * @return Then[]
     *
     * @Given /^I create a(?:n)? "([^"]*)" attribute$/
     */
    public function iCreateAnAttribute($type)
    {
        return [
            new Step\Then('I create a new attribute'),
            new Step\Then(sprintf('I choose the "%s" attribute type', $type))
        ];
    }

    /**
     * @param string $type
     *
     * @Given /^I choose the "([^"]*)" attribute type$/
     */
    public function iChooseTheAttributeType($type)
    {
        $this->getCurrentPage()->clickLink($type);
        $this->wait();
    }

    /**
     * @param TableNode $pages
     *
     * @Then /^I should be able visit the following pages without errors$/
     */
    public function iVisitTheFollowingPages(TableNode $pages)
    {
        foreach ($pages->getHash() as $data) {
            $url = $this->getSession()->evaluateScript(sprintf('return Routing.generate("%s");', $data['page']));
            $this->getMainContext()->executeScript(
                sprintf("require(['oro/navigation'], function (Nav) { Nav.getInstance().setLocation('%s'); } );", $url)
            );
            $this->wait();

            $currentUrl = $this->getSession()->getCurrentUrl();
            $currentUrl = explode('#url=', $currentUrl);
            $currentUrl = end($currentUrl);
            $currentUrl = explode('|g/', $currentUrl);
            $currentUrl = reset($currentUrl);

            assertTrue(
                $url === $currentUrl || $url . '/' === $currentUrl || $url === $currentUrl . '/',
                sprintf('Expecting the url of page "%s" to be "%s", not "%s"', $data['page'], $url, $currentUrl)
            );

            $loadedCorrectly = (bool) $this->getSession()->evaluateScript('return $(\'img[alt="Akeneo"]\').length;');
            assertTrue($loadedCorrectly, sprintf('Javascript error ocurred on page "%s"', $data['page']));
        }
    }

    /**
     * @param string $category
     *
     * @Given /^I select the "([^"]*)" tree$/
     */
    public function iSelectTheTree($category)
    {
        $this->getCurrentPage()->selectTree($category);
        $this->wait();
    }

    /**
     * @param string $category
     *
     * @Given /^I expand the "([^"]*)" category$/
     */
    public function iExpandTheCategory($category)
    {
        $this->wait(); // Make sure that the tree is loaded
        $this->getCurrentPage()->expandCategory($category);
        $this->wait();
    }

    /**
     * @param string $attribute
     *
     * @Given /^I expand the "([^"]*)" attribute$/
     */
    public function iExpandTheAttribute($attribute)
    {
        $this->getCurrentPage()->expandAttribute($attribute);
    }

    /**
     * @param string $category1
     * @param string $category2
     *
     * @Given /^I drag the "([^"]*)" category to the "([^"]*)" category$/
     */
    public function iDragTheCategoryToTheCategory($category1, $category2)
    {
        $this->getCurrentPage()->dragCategoryTo($category1, $category2);
        $this->wait();
    }

    /**
     * @param string $not
     * @param string $child
     * @param string $parent
     *
     * @Then /^I should (not )?see the "([^"]*)" category under the "([^"]*)" category$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheCategoryUnderTheCategory($not, $child, $parent)
    {
        $this->wait(); // Make sure that the tree is loaded

        $parentNode = $this->getCurrentPage()->findCategoryInTree($parent);
        $childNode  = $parentNode->getParent()->find('css', sprintf('li a:contains("%s")', $child));

        if ($not && $childNode) {
            throw $this->createExpectationException(
                sprintf('Expecting not to see category "%s" under the category "%s"', $child, $parent)
            );
        }

        if (!$not && !$childNode) {
            throw $this->createExpectationException(
                sprintf('Expecting to see category "%s" under the category "%s", not found', $child, $parent)
            );
        }
    }

    /**
     * @param string $tab
     *
     * @Given /^I visit the "([^"]*)" tab$/
     */
    public function iVisitTheTab($tab)
    {
        $tabLocator = sprintf('$("a:contains(\'%s\')").length > 0;', $tab);
        $this->wait(30000, $tabLocator);
        $this->getCurrentPage()->visitTab($tab);
        $this->wait();
    }

    /**
     * @Then /^I should (not )?see the "([^"]*)" tab$/
     */
    public function iShouldSeeTheTab($not, $tab)
    {
        $tabElement = $this->getCurrentPage()->getFormTab($tab);

        if ($not && $tabElement) {
            throw $this->createExpectationException(sprintf('Expecting not to see tab "%s"', $tab));
        }

        if (!$not && !$tabElement) {
            throw $this->createExpectationException(sprintf('Expecting to see tab "%s", not found', $tab));
        }
    }

    /**
     * @Given /^I open the history$/
     *
     * @throws ExpectationException
     */
    public function iOpenTheHistory()
    {
        $this->getCurrentPage()->openPanel('History');
        $this->getMainContext()->executeScript("$('.panel-pane.history-panel').css({'height': '90%'});");

        $expandButton = $this->getMainContext()->spin(function () {
            $expandHistory = $this->getCurrentPage()->find('css', '.expand-history');

            if ($expandHistory && $expandHistory->isValid()) {
                $expandHistory->click();

                return true;
            }

            return false;
        });

        $this->wait();
    }

    /**
     * @Then /^I should see (\d+) versions in the history$/
     */
    public function iShouldSeeVersionsInTheHistory($expectedCount)
    {
        $actualVersions = $this->getSession()->getPage()->findAll('css', '.history-panel tbody tr.product-version');

        if ((int) $expectedCount !== count($actualVersions)) {
            throw new \Exception(
                sprintf(
                    'Expecting %d versions, actually saw %d',
                    $expectedCount,
                    count($actualVersions)
                )
            );
        }
    }

    /**
     * @param string $panel
     *
     * @Given /^I open the "([^"]*)" panel$/
     */
    public function iOpenThePanel($panel)
    {
        $this->wait();
        $this->getCurrentPage()->openPanel($panel);
        $this->wait();
    }

    /**
     * @param string $panel
     *
     * @Given /^I close the "([^"]*)" panel$/
     */
    public function iCloseThePanel($panel)
    {
        $this->wait();
        $this->getCurrentPage()->closePanel($panel);
        $this->wait();
    }

    /**
     * @param string $group
     *
     * @Given /^I visit the "([^"]*)" group$/
     */
    public function iVisitTheGroup($group)
    {
        $this->getCurrentPage()->visitGroup($group);
        $this->wait();
    }

    /**
     * @param string $group
     *
     * @Given /^I click on the "([^"]*)" ACL group$/
     */
    public function iClickOnTheACLGroup($group)
    {
        $this->getCurrentPage()->selectGroup($group);
    }

    /**
     * @param string $group
     *
     * @Given /^I click on the "([^"]*)" ACL role/
     */
    public function iClickOnTheACLRole($group)
    {
        $this->getCurrentPage()->selectRole($group);
    }

    /**
     * @param string $association
     *
     * @Given /^I select the "([^"]*)" association$/
     */
    public function iSelectTheAssociation($association)
    {
        $this->getCurrentPage()->selectAssociation($association);
        $this->wait();
    }

    /**
     * @Given /^there should be (\d+) errors? in the "([^"]*)" tab$/
     */
    public function thereShouldBeErrorsInTheTab($number, $name)
    {
        $tab = $this->getCurrentPage()->getTab($name);
        if (!$tab) {
            throw $this->createExpectationException(
                sprintf('Tab "%s" not found', $name)
            );
        }

        $badge = $tab->find('css', '.invalid-badge');
        if (!$badge && 0 < (int) $number) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to find "%d" errors in the tab "%s", no errors found',
                    $number,
                    $name
                )
            );
        } elseif (!$badge && 0 === (int) $number) {
            return;
        }

        $errors = $badge->getText();
        if ($errors != $number) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to find "%d" errors in the tab "%s", found %s instead',
                    $number,
                    $name,
                    $errors
                )
            );
        }
    }

    /* -------------------- Other methods -------------------- */

    /**
     * @param string $currencies
     *
     * @When /^I (?:de)?activate the (.*) currenc(?:y|ies)$/
     */
    public function iToggleTheCurrencies($currencies)
    {
        foreach ($this->listToArray($currencies) as $currency) {
            $this->getCurrentPage()->clickOnAction($currency, 'Change status');
            $this->wait();
        }
    }

    /**
     * @param string $locale
     *
     * @When /^the locale "([^"]*)" should be selected$/
     */
    public function theLocaleShouldBeSelected($locale)
    {
        $this->getCurrentPage()->hasSelectedLocale($locale);
    }

    /**
     * @param string $locale
     *
     * @When /^I switch the locale to "([^"]*)"$/
     */
    public function iSwitchTheLocaleTo($locale)
    {
        $this->wait();
        $this->getCurrentPage()->switchLocale($locale);
        $this->wait();
    }

    /**
     * @param string $scope
     *
     * @When /^I switch the scope to "([^"]*)"$/
     */
    public function iSwitchTheScopeTo($scope)
    {
        $this->getCurrentPage()->switchScope($scope);
        $this->wait();
    }

    /**
     * @param TableNode $table
     * @param string    $productPage
     * @param bool      $copy
     *
     * @Then /^the locale switcher should contain the following items:$/
     *
     * @throws ExpectationException
     */
    public function theLocaleSwitcherShouldContainTheFollowingItems(
        TableNode $table,
        $productPage = 'edit',
        $copy = false
    ) {
        $pageName          = sprintf('Product %s', $productPage);
        $linkCount         = $this->getPage($pageName)->countLocaleLinks($copy);
        $expectedLinkCount = count($table->getHash());

        $this->spin(function () use ($pageName, $copy, $table) {
            $linkCount         = $this->getPage($pageName)->countLocaleLinks($copy);
            $expectedLinkCount = count($table->getHash());

            return $linkCount === $expectedLinkCount;
        }, 20, sprintf('Expected to see %d items in the locale switcher, saw %d', $expectedLinkCount, $linkCount));

        foreach ($table->getHash() as $data) {
            $this->spin(
                function () use ($pageName, $data, $copy) {
                    return $this->getPage($pageName)->findLocaleLink(
                        $data['locale'],
                        $data['language'],
                        $data['flag'],
                        $copy
                    );
                },
                5,
                sprintf(
                    'Could not find locale "%s %s" in the locale switcher',
                    $data['locale'],
                    $data['language']
                )
            );
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^the copy locale switcher should contain the following items:$/
     *
     * @throws ExpectationException
     */
    public function theCopyLocaleSwitcherShouldContainTheFollowingItems(TableNode $table)
    {
        $this->theLocaleSwitcherShouldContainTheFollowingItems($table, 'edit', true);
    }

    /**
     * @Given /^I confirm the ([^"]*)$/
     */
    public function iConfirmThe()
    {
        $this->getCurrentPage()->confirmDialog();

        $this->wait();
    }

    /**
     * @Given /^I cancel the ([^"]*)$/
     */
    public function iCancelThe()
    {
        $this->getCurrentPage()->cancelDialog();
    }

    /**
     * @Given /^I save the (.*)$/
     */
    public function iSave()
    {
        $this->getCurrentPage()->save();

        if (!($this->getSession()->getDriver() instanceof Selenium2Driver)) {
            $this->wait();
        }
    }

    /**
     * @Given /^I save and close$/
     */
    public function iSaveAndClose()
    {
        $this->getCurrentPage()->saveAndClose();
        $this->wait();
    }

    /**
     * @param string $attribute
     * @param int    $position
     *
     * @Given /^I change the attribute "([^"]*)" position to (\d+)$/
     */
    public function iChangeTheAttributePositionTo($attribute, $position)
    {
        $this->getCurrentPage()->dragAttributeToPosition($attribute, $position)->save();
        $this->wait();
    }

    /**
     * @param string $attribute
     * @param int    $position
     *
     * @Then /^the attribute "([^"]*)" should be in position (\d+)$/
     */
    public function theAttributeShouldBeInPosition($attribute, $position)
    {
        $actual = $this->getCurrentPage()->getAttributePosition($attribute);
        assertEquals($position, $actual);
    }

    /**
     * @param string $title
     *
     * @Then /^I should see the "([^"]*)" section$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheSection($title)
    {
        if (!$this->getCurrentPage()->getSection($title)) {
            throw $this->createExpectationException(sprintf('Expecting to see the %s section.', $title));
        }
    }

    /**
     * @param int $expectedCount
     *
     * @Given /^the Options section should contain ([^"]*) options?$/
     *
     * @throws ExpectationException
     */
    public function theOptionsSectionShouldContainOption($expectedCount = 1)
    {
        if ($expectedCount != $count = $this->getCurrentPage()->countOptions()) {
            throw $this->createExpectationException(
                sprintf('Expecting to see %d option, saw %d.', $expectedCount, $count)
            );
        }
    }

    /**
     * @param string $optionName
     *
     * @Then /^I remove the "([^"]*)" option$/
     */
    public function iRemoveTheOption($optionName)
    {
        $this->getCurrentPage()->removeOption($optionName);
    }

    /**
     * @param string $group
     * @param string $attributes
     *
     * @Given /^attributes? in group "([^"]*)" should be (.*)$/
     *
     * @throws ExpectationException
     */
    public function attributesInGroupShouldBe($group, $attributes)
    {
        $page       = $this->getCurrentPage();
        $attributes = $this->listToArray($attributes);
        $page->visitGroup($group);
        $this->wait();

        $group = $this->getFixturesContext()->findAttributeGroup($group);

        if (count($attributes) !== $actual = $page->getFieldsCount()) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected to see %d fields in group "%s", actually saw %d',
                    count($attributes),
                    $group,
                    $actual
                )
            );
        }

        $labels = array_map(
            function ($field) {
                return str_replace('*', '', $field->getText());
            },
            $page->getFields()
        );

        if (count(array_diff($attributes, $labels))) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see attributes "%s" in group "%s", but saw "%s".',
                    implode('", "', $attributes),
                    $group,
                    implode('", "', $labels)
                )
            );
        }
    }

    /**
     * @param string $title
     *
     * @Then /^the title of the product should be "([^"]*)"$/
     */
    public function theTitleOfTheProductShouldBe($title)
    {
        if ($title !== $actual = $this->getCurrentPage()->getTitle()) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected product title "%s", actually saw "%s"',
                    $title,
                    $actual
                )
            );
        }
    }

    /**
     * @param string $fieldName
     * @param string $locale
     * @param string $expected
     *
     * @Then /^the product ([^"]*) for locale "([^"]*)" should be empty$/
     * @Then /^the product ([^"]*) for locale "([^"]*)" should be "([^"]*)"$/
     * @Then /^the field ([^"]*) for locale "([^"]*)" should contain "([^"]*)"$/
     *
     * @return Then[]
     */
    public function theProductLocalizableFieldValueShouldBe($fieldName, $locale, $expected = '')
    {
        $steps = [new Step\Then(sprintf('I switch the locale to "%s"', $locale))];
        if ('' === $expected) {
            $steps[] = new Step\Then(sprintf('the product %s should be empty', $fieldName));
        } else {
            $steps[] = new Step\Then(sprintf('the product %s should be "%s"', $fieldName, $expected));
        }

        return $steps;
    }

    /**
     * @param string $fieldName
     * @param string $scope
     * @param string $expected
     *
     * @Then /^the product ([^"]*) for scope "([^"]*)" should be empty$/
     * @Then /^the product ([^"]*) for scope "([^"]*)" should be "([^"]*)"$/
     * @Then /^the field ([^"]*) for scope "([^"]*)" should contain "([^"]*)"$/
     *
     * @return Then[]
     */
    public function theProductScopableFieldValueShouldBe($fieldName, $scope, $expected = '')
    {
        $steps = [new Step\Then(sprintf('I switch the scope to "%s"', $scope))];
        if ('' === $expected) {
            $steps[] = new Step\Then(sprintf('the product %s should be empty', $fieldName));
        } else {
            $steps[] = new Step\Then(sprintf('the product %s should be "%s"', $fieldName, $expected));
        }

        return $steps;
    }

    /**
     * @param string $fieldName
     * @param string $locale
     * @param string $scope
     * @param string $expected
     *
     * @Then /^the product ([^"]*) for locale "([^"]*)" and scope "([^"]*)" should be empty$/
     * @Then /^the product ([^"]*) for locale "([^"]*)" and scope "([^"]*)" should be "([^"]*)"$/
     * @Then /^the field ([^"]*) for locale "([^"]*)" and scope "([^"]*)" should contain "([^"]*)"$/
     *
     * @return Then[]
     */
    public function theProductLocalizableAndScopableFieldValueShouldBe($fieldName, $locale, $scope, $expected = '')
    {
        $steps = [
            new Step\Then(sprintf('I switch the locale to "%s"', $locale)),
            new Step\Then(sprintf('I switch the scope to "%s"', $scope))
        ];

        if ('' === $expected) {
            $steps[] = new Step\Then(sprintf('the product %s should be empty', $fieldName));
        } else {
            $steps[] = new Step\Then(sprintf('the product %s should be "%s"', $fieldName, $expected));
        }

        return $steps;
    }

    /**
     * @param string $fieldName
     * @param string $expected
     *
     * @Then /^the product ([^"]*) should be empty$/
     * @Then /^the product ([^"]*) should be "([^"]*)"$/
     *
     * @throws \LogicException
     * @throws ExpectationException
     */
    public function theProductFieldValueShouldBe($fieldName, $expected = '')
    {
        $this->spin(function () use ($fieldName, $expected) {
            $this->getCurrentPage()->compareFieldValue($fieldName, $expected);

            return true;
        });
    }

    /**
     * @param string $label
     * @param string $expected
     *
     * @Then /^the field ([^"]*) should contain "([^"]*)"$/
     *
     * @throws \LogicException
     * @throws ExpectationException
     *
     * TODO: should be moved to a page context and theProductFieldValueShouldBe() method should be merged with this one
     */
    public function theFieldShouldContain($label, $expected)
    {
        $this->wait();
        $field = $this->getCurrentPage()->findField($label);

        if ($field->hasClass('select2-focusser')) {
            for ($i = 0; $i < 2; ++$i) {
                if (!$field->getParent()) {
                    break;
                }
                $field = $field->getParent();
            }
            if ($select = $field->find('css', 'select')) {
                $actual = $select->find('css', 'option[selected]')->getHtml();
            } else {
                $actual = trim($field->find('css', '.select2-chosen')->getHtml());
            }
        } elseif ($field->hasClass('select2-input')) {
            for ($i = 0; $i < 4; ++$i) {
                if (!$field->getParent()) {
                    break;
                }
                $field = $field->getParent();
            }
            if ($select = $field->find('css', 'select')) {
                $options = $field->findAll('css', 'option[selected]');
            } else {
                $options = $field->findAll('css', 'li.select2-search-choice div');
            }

            $actual = [];
            foreach ($options as $option) {
                $actual[] = $option->getHtml();
            }
            $expected = $this->listToArray($expected);
            sort($actual);
            sort($expected);
            $actual   = implode(', ', $actual);
            $expected = implode(', ', $expected);
        } elseif ($field->hasClass('datepicker')) {
            $actual = $field->getAttribute('value');
        } elseif ((null !== $parent = $field->getParent()) && $parent->hasClass('upload-zone')) {
            // We are dealing with an upload field
            if (null === $filename = $parent->find('css', '.upload-filename')) {
                throw new \LogicException('Cannot find filename of upload field');
            }
            $actual = $filename->getText();
        } else {
            $actual = $field->getValue();
        }

        if ($expected != $actual) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected product field "%s" to contain "%s", but got "%s".',
                    $label,
                    $expected,
                    $actual
                )
            );
        }
    }

    /**
     * @param string $not
     * @param string $choices
     * @param string $label
     *
     * @Then /^I should(?P<not> not)? see the choices? (?P<choices>.+) in (?P<label>.+)$/
     */
    public function iShouldSeeTheChoicesInField($not, $choices, $label)
    {
        $this->getCurrentPage()->checkFieldChoices($label, $this->listToArray($choices), !$not);
    }

    /**
     * @param string $label
     *
     * @Then /^the field ([^"]*) should be read only$/
     *
     * @throws \LogicException
     * @throws ExpectationException
     */
    public function theFieldShouldBeReadOnly($label)
    {
        $this->wait();
        $field = $this->getCurrentPage()->findField($label);

        if (!$field->hasAttribute('disabled')) {
            throw $this->createExpectationException(
                sprintf(
                    'Attribute %s exists but is not read only',
                    $label
                )
            );
        }
    }

    /**
     * @param string $fieldName
     * @param string $locale
     * @param string $scope
     * @param string $expected
     *
     * @Then /^the ([^"]*) copy value for scope "([^"]*)" and locale "([^"]*)" should be "([^"]*)"$/
     */
    public function theCopyValueShouldBe($fieldName, $scope, $locale, $expected)
    {
        $this->getCurrentPage()->compareWith($locale, $scope);
        $this->getCurrentPage()->compareFieldValue($fieldName, $expected, true);
    }

    /**
     * @param string $field
     * @param string $scope
     * @param string $value
     *
     * @When /^I change the ([^"]+) for scope (\w+) to "([^"]*)"$/
     *
     * @return Step\When[]
     */
    public function iChangeTheValueForScope($field, $scope, $value)
    {
        return [
            new Step\When(sprintf('I switch the scope to "%s"', $scope)),
            new Step\When(sprintf('I change the "%s" to "%s"', $field, $value))
        ];
    }

    /**
     * @param string $field
     * @param string $locale
     * @param string $value
     *
     * @When /^I change the ([^"]+) for locale (\w+) to "([^"]*)"$/
     *
     * @return Step\When[]
     */
    public function iChangeTheValueForLocale($field, $locale, $value)
    {
        return [
            new Step\When(sprintf('I switch the locale to "%s"', $locale)),
            new Step\When(sprintf('I change the %s to "%s"', $field, $value))
        ];
    }

    /**
     * @param string $field
     * @param string $scope
     * @param string $locale
     * @param string $value
     *
     * @When /^I change the ([^"]+) for scope (\w+) and locale (\w+) to "([^"]*)"$/
     *
     * @return Step\When[]
     */
    public function iChangeTheValueForScopeAndLocale($field, $scope, $locale, $value)
    {
        return [
            new Step\When(sprintf('I switch the scope to "%s"', $scope)),
            new Step\When(sprintf('I switch the locale to "%s"', $locale)),
            new Step\When(sprintf('I change the %s to "%s"', $field, $value))
        ];
    }

    /**
     * @param string $field
     * @param string $value
     * @param string $language
     *
     *
     * @When /^I change the (?P<field>\w+) to "([^"]*)"$/
     * @When /^I change the "(?P<field>[^"]*)" to "([^"]*)"$/
     * @When /^I change the (?P<language>\w+) (?P<field>\w+) to "(?P<value>[^"]*)"$/
     * @When /^I change the (?P<field>\w+) to an invalid value$/
     */
    public function iChangeTheTo($field, $value = null, $language = null)
    {
        if ($language) {
            try {
                $field = $this->getCurrentPage()->getFieldLocator($field, $this->getLocaleCode($language));
            } catch (\BadMethodCallException $e) {
                // Use default $field if current page does not provide a getFieldLocator method
            }
        }

        $value = $value !== null ? $value : $this->getInvalidValueFor(
            sprintf('%s.%s', $this->getNavigationContext()->currentPage, $field)
        );

        $this->getCurrentPage()->fillField($field, $value);
        $this->wait();
    }

    /**
     * @param string $not
     * @param string $attributes
     * @param string $group
     *
     * @Then /^I should (not )?see available attributes? (.*) in group "([^"]*)"$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeAvailableAttributesInGroup($not, $attributes, $group)
    {
        foreach ($this->listToArray($attributes) as $attribute) {
            $element = $this->getCurrentPage()->findAvailableAttributeInGroup($attribute, $group);
            if (!$not) {
                if (!$element) {
                    throw $this->createExpectationException(
                        sprintf(
                            'Expecting to see attribute %s under group %s, but was not present.',
                            $attribute,
                            $group
                        )
                    );
                }
            } else {
                if ($element) {
                    throw $this->createExpectationException(
                        sprintf(
                            'Expecting not to see attribute %s under group %s, but was present.',
                            $attribute,
                            $group
                        )
                    );
                }
            }
        }
    }

    /**
     * @param string $attributes
     *
     * @Given /^I add available attributes? (.*)$/
     */
    public function iAddAvailableAttributes($attributes)
    {
        $this->getCurrentPage()->addAvailableAttributes($this->listToArray($attributes));
        $this->wait();
    }

    /**
     * @param string $families
     *
     * @Then /^I should see the families (.*)$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheFamilies($families)
    {
        $expectedFamilies = $this->listToArray($families);

        if ($expectedFamilies !== $families = $this->getPage('Family index')->getFamilies()) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see families %s, but saw %s',
                    print_r(\Doctrine\Common\Util\Debug::export($expectedFamilies, 2), true),
                    print_r(\Doctrine\Common\Util\Debug::export($families, 2), true)
                )
            );
        }
    }

    /**
     * @param string $attributes
     * @param string $group
     *
     * @Given /^I should see attributes? "([^"]*)" in group "([^"]*)"$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeAttributesInGroup($attributes, $group)
    {
        $attributes = $this->listToArray($attributes);
        foreach ($attributes as $attribute) {
            if (!$this->getCurrentPage()->getAttribute($attribute, $group)) {
                throw $this->createExpectationException(
                    sprintf(
                        'Expecting to see attribute %s under group %s, but was not present.',
                        $attribute,
                        $group
                    )
                );
            }
        }
    }

    /**
     * @param string $not
     * @param string $field
     *
     * @Then /^I should (not )?see a remove link next to the "([^"]*)" field$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeARemoveLinkNextToTheField($not, $field)
    {
        $removeLink = $this->getPage('Product edit')->getRemoveLinkFor($field);
        if ($not) {
            if ($removeLink) {
                throw $this->createExpectationException(
                    sprintf(
                        'Remove link on field "%s" should not be displayed.',
                        $field
                    )
                );
            }
        } else {
            if (!$removeLink) {
                throw $this->createExpectationException(
                    sprintf(
                        'Remove link on field "%s" should be displayed.',
                        $field
                    )
                );
            }
        }
    }

    /**
     * @Then /^I should (not )?be able to remove the file of "([^"]*)"$/
     */
    public function iShouldBeAbleToRemoveTheFileOf($not, $field)
    {
        $removeFileButton = $this->getPage('Product edit')->getRemoveFileButtonFor($field);

        if ($not && $removeFileButton && $removeFileButton->isVisible()) {
            throw $this->createExpectationException(
                sprintf('Remove file button on field "%s" should not be displayed.', $field)
            );
        } elseif (!$not && (!$removeFileButton || !$removeFileButton->isVisible())) {
            throw $this->createExpectationException(
                sprintf('Remove file button on field "%s" should be displayed.', $field)
            );
        }
    }

    /**
     * @param string $field
     *
     * @When /^I remove the "([^"]*)" attribute$/
     *
     * @throws ExpectationException
     * @throws \Behat\Mink\Exception\ElementNotFoundException
     */
    public function iRemoveTheAttribute($field)
    {
        if (null === $link = $this->getCurrentPage()->getRemoveLinkFor($field)) {
            throw $this->createExpectationException(
                sprintf(
                    'Remove link on field "%s" should be displayed.',
                    $field
                )
            );
        }

        $link->click();
        $this->wait();
    }

    /**
     * @param string $attribute
     *
     * @Then /^I should not see the "([^"]*)" attribute$/
     */
    public function iShouldNotSeeTheAttribute($attribute)
    {
        $element = $this->getCurrentPage()->getAttribute($attribute);

        if (null !== $element) {
            throw new \RuntimeException(sprintf('Attribute "%s" found and should not be.', $attribute));
        }
    }

    /**
     * @param string $field
     *
     * @When /^I add a new option to the "([^"]*)" attribute:$/
     *
     * @throws ExpectationException
     */
    public function iAddANewOptionToTheAttribute($field, TableNode $table)
    {
        if (null === $link = $this->getCurrentPage()->getAddOptionLinkFor($field)) {
            throw $this->createExpectationException(
                sprintf(
                    'Add option link should be displayed for attribute "%s".',
                    $field
                )
            );
        }

        $link->click();

        $this->getCurrentPage()->fillPopinFields($table->getRowsHash());

        $addButton = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.modal .btn.ok');
        });

        $addButton->click();

        $this->getMainContext()->wait(10000);
    }

    /**
     * @Then /^I should see reorder handles$/
     */
    public function iShouldSeeReorderHandles()
    {
        if ($this->getCurrentPage()->countOrderableOptions() <= 0) {
            throw $this->createExpectationException('No reorder handle found');
        }
    }

    /**
     * @Then /^I should not see reorder handles$/
     */
    public function iShouldNotSeeReorderHandles()
    {
        if ($this->getCurrentPage()->countOrderableOptions() > 0) {
            throw $this->createExpectationException('Reorder handle was not expected');
        }
    }

    /**
     * @param string $attributes
     *
     * @Then /^eligible attributes as label should be (.*)$/
     *
     * @throws ExpectationException
     */
    public function eligibleAttributesAsLabelShouldBe($attributes)
    {
        $expectedAttributes = $this->listToArray($attributes);
        $options            = $this->getPage('Family edit')->getAttributeAsLabelOptions();

        if (count($expectedAttributes) !== $actual = count($options)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected to see %d eligible attributes as label, actually saw %d:'."\n%s",
                    count($expectedAttributes),
                    $actual,
                    print_r(\Doctrine\Common\Util\Debug::export($options, 2), true)
                )
            );
        }

        if ($expectedAttributes !== $options) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected to see eligible attributes as label %s, actually saw %s',
                    print_r(\Doctrine\Common\Util\Debug::export($expectedAttributes, 2), true),
                    print_r(\Doctrine\Common\Util\Debug::export($options, 2), true)
                )
            );
        }
    }

    /**
     * @param string $role
     *
     * @Given /^I select the role "([^"]*)"$/
     */
    public function iSelectRole($role)
    {
        $this->getCurrentPage()->selectRole($role);
    }

    /**
     * @param string    $popin
     * @param TableNode $table
     *
     * @Given /^I fill in the following information(| in the popin):$/
     */
    public function iFillInTheFollowingInformation($popin, TableNode $table)
    {
        $element = $popin ? $this->getCurrentPage()->find('css', '.ui-dialog') : null;
        if ($popin && !$element) {
            $element = $this->getCurrentPage()->find('css', '.modal');
        }
        foreach ($table->getRowsHash() as $field => $value) {
            $this->getCurrentPage()->fillField($field, $value, $element);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I should not see the following option:$/
     */
    public function iShouldNotSeeTheFollowingOptions(TableNode $table)
    {
        foreach ($table->getRowsHash() as $field => $value) {
            try {
                $this->getCurrentPage()->fillField($field, $value);
            } catch (\InvalidArgumentException $e) {
                $needle = sprintf('Could not find option "%s"', $value);
                if (false === strpos($e->getMessage(), $needle)) {
                    throw $e;
                }
                continue;
            }
            throw new \InvalidArgumentException(sprintf('Option "%s" has been found and should not.', $value));
        }
    }

    /**
     * @param TableNode $table
     *
     * @When /^I fill in the following information in the quick search popin:$/
     */
    public function iFillInTheFollowingInformationInTheQuickSearchPopin(TableNode $table)
    {
        $fields = $table->getRowsHash();
        if (!isset($fields['type'])) {
            $fields['type'] = null;
        }

        $this->getCurrentPage()->fillQuickSearch($fields['search'], $fields['type']);
    }

    /**
     * @When /^I open the quick search popin$/
     */
    public function iOpenTheQuickSearchPopin()
    {
        $this->getCurrentPage()->openQuickSearchPopin();
    }

    /**
     * @param TableNode $table
     *
     * @When /^I can search by the following types:$/
     */
    public function iCanSearchByTheFollowingTypes(TableNode $table)
    {
        $list = [];
        foreach ($table->getHash() as $row) {
            $list[] = $row['type'];
        }
        $this->getCurrentPage()->checkTypeSearchFieldList($list);
    }

    /**
     * @param TableNode $table
     *
     * @When /^I can not search by the following types:$/
     */
    public function iCanNotSearchByTheFollowingTypes(TableNode $table)
    {
        $list = [];
        foreach ($table->getHash() as $row) {
            $list[] = $row['type'];
        }
        $this->getCurrentPage()->checkTypeSearchFieldList($list, false);
    }

    /**
     * @param string $permission
     * @param string $resources
     *
     * @When /^I (grant|remove) rights to (.*)$/
     */
    public function iSetRightsToACLResources($permission, $resources)
    {
        $method = $permission . 'ResourceRights';
        foreach ($this->listToArray($resources) as $resource) {
            $this->getCurrentPage()->$method($resource, $permission);
        }
    }

    /**
     * @When /^I grant all rights$/
     */
    public function iGrantAllRightsToACLResources()
    {
        $this->getCurrentPage()->grantAllResourceRights();
    }

    /**
     * @param string $role
     *
     * @Given /^I reset the "([^"]*)" rights$/
     *
     * @return Then[]
     */
    public function iResetTheRights($role)
    {
        return [
            new Step\Then(sprintf('I am on the "%s" role page', $role)),
            new Step\Then('I grant all rights'),
            new Step\Then('I save the role')
        ];
    }

    /**
     * @param TableNode $table
     *
     * @Then /^removing the following permissions? should hide the following buttons?:$/
     *
     * @return Then[]
     */
    public function removingPermissionsShouldHideTheButtons(TableNode $table)
    {
        $steps = [];

        foreach ($table->getHash() as $data) {
            $steps[] = new Step\Then('I am on the "Administrator" role page');
            $steps[] = new Step\Then(sprintf('I remove rights to %s', $data['permission']));
            $steps[] = new Step\Then('I save the role');
            $steps[] = new Step\Then(sprintf('I am on the %s page', $data['page']));
            $steps[] = new Step\Then(sprintf('I should not see "%s"', $data['button']));
            if ($forbiddenPage = $data['forbiddenPage']) {
                $steps[] = new Step\Then(sprintf('I should not be able to access the %s page', $forbiddenPage));
            }
        }
        $steps[] = new Step\Then('I reset the "Administrator" rights');

        return $steps;
    }

    /**
     * @param TableNode $table
     *
     * @Then /^removing the following permissions? should hide the following section:$/
     *
     * @return Then[]
     */
    public function removingPermissionsShouldHideTheSection(TableNode $table)
    {
        $steps = [];

        foreach ($table->getHash() as $data) {
            $steps[] = new Step\Then(sprintf('I am on the %s page', $data['page']));
            $steps[] = new Step\Then(sprintf('I should see "%s"', $data['section']));
            $steps[] = new Step\Then('I am on the "Administrator" role page');
            $steps[] = new Step\Then(sprintf('I remove rights to %s', $data['permission']));
            $steps[] = new Step\Then('I save the role');
            $steps[] = new Step\Then(sprintf('I am on the %s page', $data['page']));
            $steps[] = new Step\Then(sprintf('I should not see "%s"', $data['section']));
        }
        $steps[] = new Step\Then('I reset the "Administrator" rights');

        return $steps;
    }

    /**
     * @param string $field
     *
     * @Given /^I remove the "([^"]*)" file$/
     */
    public function iRemoveTheFile($field)
    {
        $this->getMainContext()->wait();
        $script = sprintf("$('label:contains(\"%s\")').parents('.form-field').find('.clear-field').click();", $field);
        if (!$this->getMainContext()->executeScript($script)) {
            $this->getCurrentPage()->removeFileFromField($field);
        }

        $this->getSession()->executeScript('$(\'.edit .field-input input[type="file"]\').trigger(\'change\');');
        $this->getMainContext()->wait();
    }

    /**
     * @param string $link
     *
     * @Given /^I open "([^"]*)" in the current window$/
     *
     * @throws ExpectationException
     *
     * @return Step\Given
     */
    public function iOpenInTheCurrentWindow($link)
    {
        try {
            $this->getSession()->executeScript(
                "$('[target]').removeAttr('target');"
            );
            $this->wait();
            $this->getCurrentPage()
                ->find('css', sprintf('div.preview span:contains("%s")', $link))
                ->getParent()
                ->find('css', sprintf('span.open-media', $link))
                ->click();
        } catch (UnsupportedDriverActionException $e) {
            throw $this->createExpectationException('You must use selenium for this feature.');
        }
    }

    /**
     * @param TableNode $table
     *
     * @return Then[]
     *
     * @Given /^the following attribute types should have the following fields$/
     */
    public function theFollowingAttributeTypesShouldHaveTheFollowingFields(TableNode $table)
    {
        $steps = [];
        foreach ($table->getRowsHash() as $type => $fields) {
            $steps[] = new Step\Then('I am on the attributes page');
            $steps[] = new Step\Then(sprintf('I create a "%s" attribute', $type));
            $steps[] = new Step\Then(sprintf('I should see the %s fields', $fields));
        }

        return $steps;
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I create the following attribute options:$/
     */
    public function iCreateTheFollowingAttributeOptions(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->getCurrentPage()->addOption($data['Code']);
            $this->wait(3000);
        }
    }

    /**
     * @param string $oldOptionName
     * @param string $newOptionName
     *
     * @Given /^I edit the "([^"]*)" option and turn it to "([^"]*)"$/
     */
    public function iEditTheFollowingAttributeOptions($oldOptionName, $newOptionName)
    {
        $this->getCurrentPage()->editOption($oldOptionName, $newOptionName);
        $this->wait(3000);
    }

    /**
     * @param string $oldOptionName
     * @param string $newOptionName
     *
     * @Given /^I edit the code "([^"]*)" to turn it to "([^"]*)" and cancel$/
     */
    public function iEditAndCancelToEditTheFollowingAttributeOptions($oldOptionName, $newOptionName)
    {
        $this->getCurrentPage()->editOptionAndCancel($oldOptionName, $newOptionName);
        $this->wait(3000);
    }

    /**
     * @param string $button
     *
     * @Given /^I press the "([^"]*)" button$/
     */
    public function iPressTheButton($button)
    {
        $this->getMainContext()->spin(function () use ($button) {
            $this->getCurrentPage()->pressButton($button);

            return true;
        });
        $this->wait();
    }

    /**
     * @param string $button
     *
     * @Given /^I should see the "([^"]*)" button$/
     */
    public function iShouldSeeTheButton($button)
    {
        $this->getCurrentPage()->getButton($button);
    }

    /**
     * @param string $button
     *
     * @Given /^I should not see the "([^"]*)" button$/
     *
     * @throws ExpectationException
     */
    public function iShouldNotSeeTheButton($button)
    {
        if (null !== $this->getCurrentPage()->getButton($button)) {
            throw $this->createExpectationException(
                sprintf('Button "%s" should not be displayed', $button)
            );
        }
    }

    /**
     * @param string $buttonLabel
     *
     * @Given /^I press the "([^"]*)" button in the popin$/
     */
    public function iPressTheButtonInThePopin($buttonLabel)
    {
        $buttonElement = $this->spin(function () use ($buttonLabel) {
            return $this
                ->getCurrentPage()
                ->find('css', sprintf('.ui-dialog button:contains("%1$s"), .modal a:contains("%1$s")', $buttonLabel));
        });

        $buttonElement->press();
        $this->wait();
    }

    /**
     * @param string $item
     * @param string $button
     *
     * @Given /^I press "([^"]*)" on the "([^"]*)" dropdown button$/
     */
    public function iPressOnTheDropdownButton($item, $button)
    {
        $this
            ->getCurrentPage()
            ->getDropdownButtonItem($item, $button)
            ->click();
        $this->wait();
    }

    /**
     * @param string $action
     *
     * @Given /^I (enable|disable) the product$/
     */
    public function iEnableOrDisableTheProduct($action)
    {
        $action = $action . 'Product';
        $this->getCurrentPage()->$action()->save();
        $this->wait();
    }

    /**
     * @param string $action
     *
     * @Given /^I (enable|disable) the products$/
     */
    public function iEnableOrDisableTheProducts($action)
    {
        $status = $action === 'enable' ? true : false;
        $this->getCurrentPage()->toggleSwitch('To enable', $status);
        $this->getCurrentPage()->next();
        $this->getCurrentPage()->confirm();
        $this->wait();
    }

    /**
     * @Then /^I choose to download the file$/
     */
    public function iChooseToDownloadTheFile()
    {
        throw new \RuntimeException('Download file is not yet implemented');
    }

    /**
     * @param string $status
     * @param string $locator
     *
     * @When /^I (un)?check the "([^"]*)" switch$/
     */
    public function iCheckTheSwitch($status, $locator)
    {
        $this->getCurrentPage()->toggleSwitch($locator, $status === '');
        $this->wait();
    }

    /**
     * @param string $status
     *
     * @When /^I (en|dis)able the inclusion of sub-categories$/
     */
    public function iSwitchTheSubCategoriesInclusion($status)
    {
        $switch = $this->spin(function () {
            return $this->getCurrentPage()->findById('nested_switch_input');
        }, 5);

        $on = 'en' === $status;
        if ($switch->isChecked() !== $on) {
            $switch->getParent()->find('css', 'label')->click();
        }
        $this->wait();
    }

    /**
     * @param Product $product
     *
     * @Given /^(product "([^"]*)") should be disabled$/
     *
     * @throws ExpectationException
     */
    public function productShouldBeDisabled(Product $product)
    {
        $this->getMainContext()->getSmartRegistry()->getManagerForClass(get_class($product))->refresh($product);
        if ($product->isEnabled()) {
            throw $this->createExpectationException('Product was expected to be be disabled');
        }
    }

    /**
     * @param Product $product
     *
     * @Given /^(product "([^"]*)") should be enabled$/
     *
     * @throws ExpectationException
     */
    public function productShouldBeEnabled(Product $product)
    {
        $this->getMainContext()->getSmartRegistry()->getManagerForClass(get_class($product))->refresh($product);
        if (!$product->isEnabled()) {
            throw $this->createExpectationException('Product was expected to be be enabled');
        }
    }

    /**
     * @param string      $sku
     * @param string|null $expectedFamily
     *
     * @Then /^the product "([^"]*)" should have no family$/
     * @Then /^the family of (?:the )?product "([^"]*)" should be "([^"]*)"$/
     */
    public function theFamilyOfProductShouldBe($sku, $expectedFamily = '')
    {
        $this->clearUOW();
        $product = $this->getFixturesContext()->getProduct($sku);

        $actualFamily = $product->getFamily() ? $product->getFamily()->getCode() : '';
        assertEquals(
            $expectedFamily,
            $actualFamily,
            sprintf('Expecting the family of "%s" to be "%s", not "%s".', $sku, $expectedFamily, $actualFamily)
        );
    }

    /**
     * @param int $count
     *
     * @Then /^there should be (\d+) updates?$/
     *
     * @throws ExpectationException
     */
    public function thereShouldBeUpdate($count)
    {
        $historyRows = $this->spin(function () use ($count) {
            return $this->getCurrentPage()->getHistoryRows();
        });

        if ((int) $count !== $actualCount = count($historyRows)) {
            throw $this->createExpectationException(sprintf('Expected %d updates, saw %d.', $count, $actualCount));
        }
    }

    /**
     * @param string $right
     * @param string $category
     *
     * @Given /^I (right )?click on the "([^"]*)" category$/
     */
    public function iClickOnTheCategory($right, $category)
    {
        $category = $this->getCurrentPage()->findCategoryInTree($category);

        if ($right) {
            $category->rightClick();
        } else {
            try {
                $checkbox = $this->spin(function () use ($category) {
                    return $category->find('css', '.jstree-checkbox');

                });
            } catch (\Exception $e) {
                $checkbox = null;
            }

            if (null !== $checkbox) {
                $checkbox->click();
            } else {
                $category->click();
            }
            $this->wait();
        }
    }

    /**
     * @Then /^I should see (\d+) category count$/
     *
     * @param int $count
     *
     * @throws ExpectationException
     */
    public function iShouldSeeCategoryCount($count)
    {
        $badge = $this->getCurrentPage()->find('css', sprintf('span.badge:contains("%d")', $count));
        if (!$badge) {
            throw $this->createExpectationException('Catgeroy badge not found');
        }
    }

    /**
     * @param string $action
     *
     * @Given /^I click on "([^"]*)" in the right click menu$/
     */
    public function iClickOnInTheRightClickMenu($action)
    {
        $this->getCurrentPage()->rightClickAction($action);
        $this->wait();
    }

    /**
     * @Given /^I click on the job tracker button on the job widget$/
     */
    public function iClickOnTheJobTrackerButtonOnTheJobWidget()
    {
        $this->getCurrentPage()->find('css', 'a#btn-show-list')->click();
        $this->wait();
    }

    /**
     * @Given /^I blur the category node$/
     */
    public function iBlurTheCategoryNode()
    {
        $this->getCurrentPage()->find('css', '#container')->click();
        $this->wait();
    }

    /**
     * @param string $message
     * @param string $property
     *
     * @Then /^I should see "([^"]*)" next to the (\w+)$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeNextToThe($message, $property)
    {
        if ($message !== $error = $this->getCurrentPage()->getPropertyErrorMessage($property)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see "%s" next to the %s property, but saw "%s"',
                    $message,
                    $property,
                    $error
                )
            );
        }
    }

    /**
     * @param string $code
     *
     * @When /^I wait for the "([^"]*)" job to finish$/
     */
    public function iWaitForTheJobToFinish($code)
    {
        $condition = '$("#status").length && /(COMPLETED|STOPPED|FAILED)$/.test($("#status").text().trim())';

        try {
            $this->wait(120000, $condition);
        } catch (BehaviorException $e) {
            $jobInstance  = $this->getFixturesContext()->getJobInstance($code);
            $jobExecution = $jobInstance->getJobExecutions()->first();
            $log          = $jobExecution->getLogFile();

            if (is_file($log)) {
                $dir = getenv('WORKSPACE');
                $id  = getenv('BUILD_ID');

                if (false !== $dir && false !== $id) {
                    $target = sprintf('%s/../builds/%s/batch_log/%s', $dir, $id, pathinfo($log, PATHINFO_BASENAME));

                    $fs = new \Symfony\Component\Filesystem\Filesystem();
                    $fs->copy($log, $target);

                    $log = sprintf(
                        'http://ci.akeneo.com/screenshots/%s/%s/batch_log/%s',
                        getenv('JOB_NAME'),
                        $id,
                        pathinfo($log, PATHINFO_BASENAME)
                    );
                }

                $message = sprintf('Job "%s" failed, log available at %s', $code, $log);
                $this->getMainContext()->addErrorMessage($message);
            } else {
                $this->getMainContext()->addErrorMessage(sprintf('Job "%s" failed, no log available', $code));
            }

            // Get and print the normalized jobexecution to ease debugging
            $this->getSession()->executeScript(
                sprintf(
                    '$.get("/%s/%s_execution/%d.json", function (resp) { window.executionLog = resp; });',
                    $jobInstance->getType() === 'import' ? 'collect' : 'spread',
                    $jobInstance->getType(),
                    $jobExecution->getId()
                )
            );
            $this->wait(2000);
            $executionLog = $this->getSession()->evaluateScript("return window.executionLog;");
            $this->getMainContext()->addErrorMessage(sprintf('Job execution: %s', print_r($executionLog, true)));

            // Call the wait method again to trigger timeout failure
            $this->wait(100, $condition);
        }
    }

    /**
     * @Given /^I wait for the "([^"]*)" mass-edit job to finish$/
     *
     * @param string $operationAlias
     *
     * @throws ExpectationException
     */
    public function iWaitForTheMassEditJobToFinish($operationAlias)
    {
        $operationRegistry = $this->getMainContext()
            ->getContainer()
            ->get('pim_enrich.mass_edit_action.operation.registry');

        $operation = $operationRegistry->get($operationAlias);

        if (null === $operation) {
            throw $this->createExpectationException(
                sprintf('Operation with alias "%s" doesn\'t exist', $operationAlias)
            );
        }

        if (!$operation instanceof BatchableOperationInterface) {
            throw $this->createExpectationException(
                sprintf('Can\'t get the job code from the "%s" operation', $operationAlias)
            );
        }

        $code = $operation->getBatchJobCode();

        $this->waitForMassEditJobToFinish($code);
    }

    /**
     * @Given /^I wait for the quick export to finish$/
     */
    public function iWaitForTheQuickExportToFinish()
    {
        $this->waitForMassEditJobToFinish('csv_product_quick_export');
    }

    /**
     * @Given /^I wait for (the )?widgets to load$/
     */
    public function iWaitForTheWidgetsToLoad()
    {
        $this->wait(2000, false);
        $this->wait();
    }

    /**
     * @Given /^I wait for (the )?options to load$/
     */
    public function iWaitForTheOptionsToLoad()
    {
        $this->wait(2000, false);
        $this->wait();
    }

    /**
     * @param string    $fileName
     * @param TableNode $table
     *
     * @Given /^the category order in the file "([^"]*)" should be following:$/
     *
     * @throws ExpectationException
     */
    public function theCategoryOrderInTheFileShouldBeFollowing($fileName, TableNode $table)
    {
        $fileName = $this->replacePlaceholders($fileName);
        if (!file_exists($fileName)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $fileName));
        }

        $categories = [];
        foreach (array_keys($table->getRowsHash()) as $category) {
            $categories[] = $category;
        }

        $file = fopen($fileName, 'rb');
        fgets($file);

        while (false !== $row = fgets($file)) {
            $category = array_shift($categories);
            assertSame(0, strpos($row, $category), sprintf('Expecting category "%s", saw "%s"', $category, $row));
        }

        fclose($file);
    }

    /**
     * @param string $original
     * @param string $target
     *
     * @Given /^I copy the file "([^"]*)" to "([^"]*)"$/
     *
     * @throws ExpectationException
     */
    public function iCopyTheFileTo($original, $target)
    {
        if (!file_exists($original)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $original));
        }

        copy($original, $target);
    }

    /**
     * @Then /^I should see the uploaded image$/
     */
    public function iShouldSeeTheUploadedImage()
    {
        $maxTime = 10000;

        while ($maxTime > 0) {
            $this->wait(2000, false);
            $maxTime -= 1000;
            if ($this->getPage('Product edit')->getImagePreview()) {
                return;
            }
        }

        throw $this->createExpectationException('Image preview is not displayed.');
    }

    /**
     * @param string $path
     *
     * @Then /^I should see the "([^"]*)" content$/
     */
    public function iShouldSeeTheContent($path)
    {
        if ($filesPath = $this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($filesPath), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$path;
            if (is_file($fullPath)) {
                $path = $fullPath;
            }
        }

        $this->assertSession()->responseContains(file_get_contents($path));
    }

    /**
     * @param string $attribute
     * @param string $not
     * @param string $channels
     *
     * @Then /^attribute "([^"]*)" should( not)? be required in channels? (.*)$/
     *
     * @throws ExpectationException
     */
    public function attributeShouldBeRequiredInChannels($attribute, $not, $channels)
    {
        $channels    = $this->listToArray($channels);
        $expectation = $not === '';
        foreach ($channels as $channel) {
            if ($expectation !== $this->getCurrentPage()->isAttributeRequired($attribute, $channel)) {
                throw $this->createExpectationException(
                    sprintf(
                        'Attribute %s should be%s required in channel %s',
                        $attribute,
                        $not,
                        $channel
                    )
                );
            }
        }
    }

    /**
     * @param string $attribute
     * @param string $channel
     *
     * @Given /^I switch the attribute "([^"]*)" requirement in channel "([^"]*)"$/
     */
    public function iSwitchTheAttributeRequirementInChannel($attribute, $channel)
    {
        $this->getCurrentPage()->switchAttributeRequirement($attribute, $channel);
    }

    /**
     * @Then /^I should see the completeness summary$/
     */
    public function iShouldSeeTheCompletenessSummary()
    {
        $this->getCurrentPage()->findCompletenessContent();
    }

    /**
     * @param TableNode $table
     *
     * @Then /^I should see the completeness:$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeTheCompleteness(TableNode $table)
    {
        $this->wait();
        $collapseSwitchers = $this->getCurrentPage()->findAll('css', '.completeness-block header .btn');

        foreach ($collapseSwitchers as $switcher) {
            /** @var NodeElement $switcher */
            if ('true' === $switcher->getParent()->getParent()->getAttribute('data-closed')) {
                $switcher->click();
            }
        }

        foreach ($table->getHash() as $data) {
            $channel = $data['channel'];
            $locale  = $data['locale'];

            try {
                $this->getCurrentPage()->checkCompletenessState($channel, $locale, $data['state']);
                $this->getCurrentPage()->checkCompletenessRatio($channel, $locale, $data['ratio']);
                if (isset($data['missing_values'])) {
                    $this->getCurrentPage()->checkCompletenessMissingValues($channel, $locale, $data['missing_values']);
                }
            } catch (\InvalidArgumentException $e) {
                throw $this->createExpectationException($e->getMessage());
            }
        }
    }

    /**
     * @param string $channel
     * @param string $ratio
     *
     * @Given /^completeness of "([^"]*)" should be "([^"]*)"$/
     */
    public function completenessOfShouldBe($channel, $ratio)
    {
        $actual = $this->getCurrentPage()->getChannelCompleteness($channel);
        assertEquals(
            $ratio,
            $actual,
            sprintf(
                'Expecting completeness ratio of channel "%s" to be "%s", actually was "%s"',
                $channel,
                $ratio,
                $actual
            )
        );
    }

    /**
     * @param string $lang
     * @param string $channel
     * @param string $ratio
     *
     * @Given /^"([^"]*)" completeness of "([^"]*)" should be "([^"]*)"$/
     */
    public function localizedCompletenessOfShouldBe($lang, $channel, $ratio)
    {
        $actual = $this->getCurrentPage()->getLocalizedChannelCompleteness($channel, $lang);
        assertEquals(
            $ratio,
            $actual,
            sprintf(
                'Expecting "%s" completeness ratio of channel "%s" to be "%s", actually was "%s"',
                $lang,
                $channel,
                $ratio,
                $actual
            )
        );
    }

    /**
     * @param string $attribute
     * @param string $locale
     * @param string $channel
     *
     * @Then /^I click on the missing "([^"]*)" value for "([^"]*)" locale and "([^"]*)" channel/
     */
    public function iClickOnTheMissingValueForLocaleAndChannel($attribute, $locale, $channel)
    {
        $cell = $this->getCurrentPage()->findCompletenessCell($channel, $locale);

        $link = $this->spin(function () use ($attribute, $cell) {
            return $cell->find('css', sprintf(".missing-attributes [data-attribute='%s']", $attribute));
        }, 20, sprintf("Can't find missing '%s' value link for %s/%s", $attribute, $locale, $channel));

        $link->click();
    }

    /**
     * @param string $group
     *
     * @Then /^I should be on the "([^"]*)" attribute group$/
     */
    public function iShouldBeOnTheAttributeGroup($group)
    {
        $groupNode = $this->getCurrentPage()->getAttributeGroupTab($group);

        assertTrue(
            $groupNode->hasClass('active'),
            sprintf('Expected to be on attribute group "%s"', $group)
        );
    }

    /**
     * @param string $email
     *
     * @Given /^an email to "([^"]*)" should have been sent$/
     */
    public function anEmailToShouldHaveBeenSent($email)
    {
        $recorder = $this->getMainContext()->getMailRecorder();
        if (0 === count($recorder->getMailsSentTo($email))) {
            throw $this->createExpectationException(
                sprintf(
                    'No emails were sent to %s.',
                    $email
                )
            );
        }
    }

    /**
     * @param int $seconds
     *
     * @Then /^I wait (\d+) seconds$/
     */
    public function iWaitSeconds($seconds)
    {
        $this->wait($seconds * 1000, false);
    }

    /**
     * @param string $operation
     *
     * @Given /^I choose the "([^"]*)" operation$/
     */
    public function iChooseTheOperation($operation)
    {
        $this->getNavigationContext()->currentPage = $this
            ->getPage('Batch Operation')
            ->chooseOperation($operation)
            ->next();

        $this->wait();
    }

    /**
     * @param string $fields
     *
     * @Given /^I display the (.*) attributes?$/
     */
    public function iDisplayTheAttributes($fields)
    {
        $this->getCurrentPage()->addAvailableAttributes($this->listToArray($fields));
        $this->wait();
    }

    /**
     * @Given /^I move on to the next step$/
     */
    public function iMoveOnToTheNextStep()
    {
        $this->scrollContainerTo(900);
        $this->wait(10000, '$(".btn:contains(\'Next\')").length > 0');
        $this->getCurrentPage()->next();
        $this->scrollContainerTo(900);
        $this->getCurrentPage()->confirm();
        $this->wait();
    }

    /**
     * @Then /^I click on the Akeneo logo$/
     */
    public function iClickOnTheAkeneoLogo()
    {
        $this->getCurrentPage()->clickOnAkeneoLogo();
    }

    /**
     * @When /^I start the copy$/
     */
    public function iStartTheCopy()
    {
        $this->getCurrentPage()->startCopy();
    }

    /**
     * @param string $locale
     *
     * @When /^I compare values with the "([^"]*)" translation$/
     */
    public function iCompareValuesWithTheTranslation($locale)
    {
        $this->getCurrentPage()->compareWith($locale);
    }

    /**
     * @param string $field
     *
     * @Given /^I select translations for "([^"]*)"$/
     */
    public function iSelectTranslationsFor($field)
    {
        $this->getCurrentPage()->manualSelectTranslation($field);
    }

    /**
     * @param string $mode
     *
     * @Given /^I select (.*) translations$/
     */
    public function iSelectTranslations($mode)
    {
        $this->getCurrentPage()->autoSelectTranslations(ucfirst($mode));
    }

    /**
     * @Given /^I copy selected translations$/
     */
    public function iCopySelectedTranslations()
    {
        $this->getCurrentPage()->copySelectedTranslations();
    }

    /**
     * @param string    $groupField
     * @param TableNode $fields
     *
     * @Given /^I should see "([^"]*)" fields:$/
     */
    public function iShouldSeeFields($groupField, TableNode $fields)
    {
        foreach ($fields->getRows() as $data) {
            $this->getCurrentPage()->findFieldInAccordion($groupField, $data[0]);
        }
    }

    /**
     * @param string       $code
     * @param PyStringNode $data
     *
     * @Given /^the invalid data file of "([^"]*)" should contain:$/
     *
     * @throws ExpectationException
     */
    public function theInvalidDataFileOfShouldContain($code, PyStringNode $data)
    {
        $jobInstance = $this->getMainContext()->getSubcontext('fixtures')->getJobInstance($code);

        $jobExecution = $jobInstance->getJobExecutions()->first();
        $archivist    = $this->getMainContext()->getContainer()->get('pim_base_connector.event_listener.archivist');
        $file         = $archivist->getArchive($jobExecution, 'invalid', 'invalid_items.csv');

        $file->open(new \Gaufrette\StreamMode('r'));
        $content = $file->read(1024);
        while (!$file->eof()) {
            $content .= $file->read(1024);
        }

        if ($content !== (string) $data) {
            throw $this->createExpectationException(
                sprintf("Invalid data file contains:\n\"\"\"\n%s\n\"\"\"", $content)
            );
        }
    }

    /**
     * @param PyStringNode $string
     *
     * @Given /^I execute javascript:$/
     */
    public function iExecuteJavascript(PyStringNode $string)
    {
        $this->getSession()->executeScript((string) $string);
        $this->wait();
    }

    /**
     * @param int $y
     *
     * @Given /^I scroll down$/
     */
    public function scrollContainerTo($y = 400)
    {
        $this->getSession()->executeScript(sprintf('$(".scrollable-container").scrollTop(%d);', $y));
    }

    /**
     * @param TableNode $table
     *
     * @throws ExpectationException
     *
     * @return array
     *
     *
     * @Given /^I should see the following product comments:$/
     */
    public function iShouldSeeTheFollowingProductComments(TableNode $table)
    {
        $comments = [];

        foreach ($table->getHash() as $data) {
            try {
                $author               = $this->getFixturesContext()->getUser($data['author']);
                $authorName           = $author->getFirstName() . ' ' . $author->getLastName();
                $comment              = $this->getCurrentPage()->findComment($data['message'], $authorName);
                $comments[$data['#']] = $comment;

                if (!empty($data['parent'])) {
                    $expectedParent = $comments[$data['parent']];
                    if (true !== $this->getCurrentPage()->isReplyOfComment($comment, $expectedParent)) {
                        throw $this->createExpectationException(
                            sprintf('The comment #%s is not a reply of the comment #%s', $data['#'], $data['parent'])
                        );
                    }
                }
            } catch (\LogicException $e) {
                throw $this->createExpectationException($e->getMessage());
            }
        }

        return $comments;
    }

    /**
     * @param string $message
     *
     * @When /^I delete the "([^"]*)" comment$/
     */
    public function iDeleteTheComment($message)
    {
        $username   = $this->getMainContext()->getSubcontext('fixtures')->getUsername();
        $author     = $this->getFixturesContext()->getUser($username);
        $authorName = $author->getFirstName() . ' ' . $author->getLastName();
        $comment    = $this->getCurrentPage()->findComment($message, $authorName);

        $this->getCurrentPage()->deleteComment($comment);
        $this->wait();
    }

    /**
     * @param string $message
     * @param string $author
     *
     * @throws ExpectationException
     *
     * @return bool
     *
     * @Then /^I should not see the link to delete the "([^"]*)" comment of "([^"]*)"$/
     */
    public function iShouldNotSeeTheLinkToDeleteTheComment($message, $author)
    {
        $author     = $this->getFixturesContext()->getUser($author);
        $authorName = $author->getFirstName() . ' ' . $author->getLastName();
        $comment    = $this->getCurrentPage()->findComment($message, $authorName);

        try {
            $this->getCurrentPage()->deleteComment($comment);
        } catch (\LogicException $e) {
            // the delete link is missing, that's ok
            return true;
        }

        throw $this->createExpectationException(
            sprintf('Expecting not to see link to delete the comment "%s"', $message)
        );
    }

    /**
     * @param string $message
     *
     * @When /^I add a new comment "([^"]*)"$/
     */
    public function iAddANewComment($message)
    {
        $this->getCurrentPage()->createComment($message);
        $this->wait();
    }

    /**
     * @param string $comment
     * @param string $author
     * @param string $reply
     *
     * @When /^I reply to the comment "([^"]*)" of "([^"]*)" with "([^"]*)"$/
     */
    public function iReplyToTheCommentWith($comment, $author, $reply)
    {
        $author     = $this->getFixturesContext()->getUser($author);
        $authorName = $author->getFirstName() . ' ' . $author->getLastName();
        $comment    = $this->getCurrentPage()->findComment($comment, $authorName);

        $this->getCurrentPage()->replyComment($comment, $reply);
        $this->wait();
    }

    /**
     * @param string $contentType
     *
     * @Then /^the response content type should be "([^"]*)"$/
     */
    public function contentTypeShouldBe($contentType)
    {
        $headers = $this->getSession()->getResponseHeaders();

        assertTrue(in_array($contentType, $headers['content-type']));
    }

    /**
     * @Then /^I change the family of the product to "([^"]*)"$/
     */
    public function iChangeTheFamilyOfTheProductTo($family)
    {
        $this->getCurrentPage()->changeFamily($family);
    }

    /**
     * Clear the Unit of Work
     */
    public function clearUOW()
    {
        foreach ($this->getSmartRegistry()->getManagers() as $manager) {
            $manager->clear();
        }
    }

    /**
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected function getSmartRegistry()
    {
        return $this->getMainContext()->getSmartRegistry();
    }

    /**
     * @param string $page
     * @param array  $options
     *
     * @return Page
     */
    protected function openPage($page, array $options = [])
    {
        $page = $this->getNavigationContext()->openPage($page, $options);
        $this->wait();

        return $page;
    }

    /**
     * @return Page
     */
    protected function getCurrentPage()
    {
        return $this->getNavigationContext()->getCurrentPage();
    }

    /**
     * @param string $field
     *
     * @return string
     */
    protected function getInvalidValueFor($field)
    {
        switch (strtolower($field)) {
            case 'family edit.code':
                return 'inv@lid';
            case 'attribute creation.code':
                return $this->lorem(20);
            case 'attribute creation.description':
                return $this->lorem(256);
            case 'product edit.sku':
            case 'product edit.description':
                return str_repeat('foobar ', 50);
            case 'product edit.longtext':
                return str_repeat('foobar ', 9500);
            case 'batch editcommonattributes.comment':
                return str_repeat('foobar ', 40);
            default:
                return '!@#-?_'.$this->lorem(250);
        }
    }

    /**
     * @param int $length
     *
     * @return string
     */
    protected function lorem($length = 100)
    {
        $lorem = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore'
            .'et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut'
            .'aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum'
            .'dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui'
            .'officia deserunt mollit anim id est laborum.';

        while (strlen($lorem) < $length) {
            $lorem .= ' ' . $lorem;
        }

        return substr($lorem, 0, $length);
    }

    /**
     * @param int    $time
     * @param string $condition
     */
    protected function wait($time = 20000, $condition = null)
    {
        $this->getMainContext()->wait($time, $condition);
    }

    /**
     * @return FixturesContext
     */
    protected function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    /**
     * @return NavigationContext
     */
    protected function getNavigationContext()
    {
        return $this->getMainContext()->getSubcontext('navigation');
    }

    /**
     * @param string $list
     *
     * @return array
     */
    protected function listToArray($list)
    {
        return $this->getMainContext()->listToArray($list);
    }

    /**
     * @param string $language
     *
     * @return string
     */
    protected function getLocaleCode($language)
    {
        return $this->getFixturesContext()->getLocaleCode($language);
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

    /**
     * @param $code
     */
    protected function waitForMassEditJobToFinish($code)
    {
        $jobInstance = $this->getFixturesContext()->getJobInstance($code);
        // Force to retrieve its job executions
        $jobInstance->getJobExecutions()->setInitialized(false);
        $jobExecution = $jobInstance->getJobExecutions()->last();

        $this->openPage('massEditJob show', ['id' => $jobExecution->getId()]);

        $this->iWaitForTheJobToFinish($code);
    }

    /**
     * @Then /^I should (not )?see the status-switcher button$/
     */
    public function iShouldSeeTheStatusSwitcherButton($not)
    {
        $statusSwitcher = $this->getCurrentPage()->getStatusSwitcher();

        if ($not) {
            if ($statusSwitcher && $statusSwitcher->isVisible()) {
                throw $this->createExpectationException('Status switcher should not be visible');
            }
        } else {
            if (!$statusSwitcher || !$statusSwitcher->isVisible()) {
                throw $this->createExpectationException('Status switcher should be visible');
            }
        }
    }

    /**
     * Check the user API key
     *
     * @Then /^The API key should (not )?be (.+)$/
     */
    public function theApiKeyShouldBe($not, $value)
    {
        $apiKey = $this->getCurrentPage()->getApiKey();

        if ($not) {
            if ($apiKey === $value) {
                throw $this->createExpectationException('API key should not be ' . $apiKey);
            }
        } else {
            if ($apiKey !== $value) {
                throw $this->createExpectationException('API key should be ' . $apiKey);
            }
        }
    }
}
