<?php

namespace Context;

use Behat\ChainedStepsExtension\Step;
use Behat\ChainedStepsExtension\Step\Then;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Element\NodeElement;
use Behat\Mink\Exception\ElementNotFoundException;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Context\Spin\SpinCapableTrait;
use Context\Spin\SpinException;
use Context\Spin\TimeoutException;
use Context\Traits\ClosestTrait;
use PHPUnit\Framework\Assert;
use Pim\Behat\Context\PimContext;
use Pim\Component\Catalog\Model\ProductInterface;
use SensioLabs\Behat\PageObjectExtension\PageObject\Page;

/**
 * Context of the website
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebUser extends PimContext
{
    use SpinCapableTrait;
    use ClosestTrait;

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
        $this->spin(function () use ($entity) {
            if (null !== $this->getCurrentPage()->find('css', '.modal, .ui-dialog')) {
                return true;
            }

            $this->getPage(sprintf('%s index', $entity))->clickCreationLink();

            return false;
        }, sprintf('Cannot create a new %s: cannot click on the creation link', $entity));

        $this->getNavigationContext()->currentPage = sprintf('%s creation', $entity);
    }

    /**
     * @param string $type
     *
     * @Given /^I create a product$/
     */
    public function iCreateAProduct()
    {
        $this->iCreateANew('Product');

        $this->getCurrentPage()->pressButton('Product');
    }

    /**
     * @param string $type
     *
     * @Given /^I create a product model$/
     */
    public function iCreateAProductModel()
    {
        $this->iCreateANew('Product');

        $this->getCurrentPage()->pressButton('Product model');
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
        $this->getCurrentPage()->selectAttributeType($type);
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
                sprintf("Backbone.history.navigate('#%s');", $url)
            );
            $this->wait();

            $currentUrl = $this->getSession()->getCurrentUrl();
            $currentUrl = explode('#', $currentUrl);
            $currentUrl = end($currentUrl);
            $currentUrl = explode('|g/', $currentUrl);
            $currentUrl = reset($currentUrl);

            Assert::assertTrue(
                $url === $currentUrl || $url . '/' === $currentUrl || $url === $currentUrl . '/',
                sprintf('Expecting the url of page "%s" to be "%s", not "%s"', $data['page'], $url, $currentUrl)
            );

            $loadedCorrectly = (bool) $this->getSession()->evaluateScript('return $(\'img[alt="Akeneo"]\').length;');
            Assert::assertTrue($loadedCorrectly, sprintf('Javascript error ocurred on page "%s"', $data['page']));
        }
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
     * @param string $tab
     *
     * @Given /^I visit the "([^"]*)" tab$/
     */
    public function iVisitTheTab($tab)
    {
        $this->scrollContainerTo(-1000);

        return $this->getCurrentPage()->visitTab($tab);
    }

    /**
     * @param string $tab
     *
     * @Given /^I visit the "([^"]*)" column tab$/
     */
    public function iVisitTheColumnTab($tab)
    {
        return $this->getCurrentPage()->visitColumnTab($tab);
    }

    /**
     * @param string $tab
     *
     * @throws ExpectationException
     *
     * @Then /^I should be on the "([^"]*)" tab$/
     */
    public function iShouldBeOnTheTab($tab)
    {
        $tabElement = $this->getCurrentPage()->getFormTab($tab);
        if (null === $tabElement) {
            throw $this->createExpectationException(sprintf('Cannot find form tab "%s"', $tab));
        }

        if (null === $tabElement || !$tabElement->getParent()->hasClass('active')) {
            throw $this->createExpectationException(sprintf('We are not in the %s tab', $tab));
        }
    }

    /**
     * @param string $tabName
     *
     * @Then /^I should be on the "([^"]*)" column tab$/
     */
    public function iShouldBeOnTheColumnTab($tabName)
    {
        $this->spin(function () use ($tabName) {
            $tab = $this->getCurrentPage()->getCurrentColumnTab($tabName);

            return null !== $tab && $tabName === trim($tab->getText());
        }, sprintf('Failed to check current column tab is "%s"', $tabName));
    }

    /**
     * @Then /^I should see the "([^"]*)" tab$/
     */
    public function iShouldSeeTheTab($tab)
    {
        Assert::assertNotNull($this->getCurrentPage()->getFormTab($tab));
    }

    /**
     * @param $not     string|null
     * @param $tabName string
     *
     * @Then /^I should (?P<not>not )?see the "(?P<tabName>[^"]*)" column tab$/
     */
    public function iShouldSeeTheColumnTab($tabName, $not = null)
    {
        $this->spin(function () use ($not, $tabName) {
            $found = false;
            foreach ($this->getCurrentPage()->getColumnTabs() as $tab) {
                if (trim($tab->getText()) === $tabName) {
                    $found = true;
                }
            }

            if (!$not) {
                return $found;
            } else {
                return !$found;
            }
        }, sprintf('Expected to %ssee the "%s" column tab', $not, $tabName));
    }

    /**
     * @param string $tab
     *
     * @Then /^I should not see the "([^"]*)" tab$/
     */
    public function iShouldNotSeeTheTab($tab)
    {
        Assert::assertNull($this->getCurrentPage()->getFormTab($tab));
    }

    /**
     * @param string $expectedCount
     *
     * @Then /^I should see (\d+) versions in the history$/
     */
    public function iShouldSeeVersionsInTheHistory($expectedCount)
    {
        $this->spin(function () use ($expectedCount) {
            $actualVersions = $this->getSession()->getPage()->findAll('css', '.history-panel tbody tr.entity-version');

            return ((int) $expectedCount) === count($actualVersions);
        }, sprintf(
            'Fail asserting %d versions count',
            $expectedCount
        ));
    }

    /**
     * @param string      $group
     * @param string|null $type
     *
     * @Given /^I visit the "([^"]*)" (group|association type|tree|target)$/
     */
    public function iVisitTheGroup($group, $type)
    {
        $this->scrollContainerTo(-1000);

        $this->getCurrentPage()->visitGroup($group, ucfirst($type));
    }

    /**
     * @param string $type
     *
     * @Given /^I open the (group|association type|tree) selector$/
     */
    public function iOpenTheGroup($type)
    {
        $this->getCurrentPage()->openGroupSelector(ucfirst($type));
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
     * @Then /^there should be (\d+) errors? in the "([^"]*)" tab$/
     *
     * @param $expectedErrorsCount
     * @param $tabName
     */
    public function thereShouldBeErrorsInTheTab($expectedErrorsCount, $tabName)
    {
        $this->scrollContainerTo(-1000);

        $tab = $this->getCurrentPage()->getTab($tabName);

        $this->spin(function () use ($tab, $expectedErrorsCount) {
            return $this->getTabErrorsCount($tab) === intval($expectedErrorsCount);
        }, sprintf(
            'Expecting to see %d errors on tab "%s", found %s',
            $expectedErrorsCount,
            $tabName,
            $this->getTabErrorsCount($tab)
        ));
    }

    /**
     * @When /^I click on the "([^"]*)" required attribute indicator$/
     *
     * @param $attributeGroup
     */
    public function iClickOnAttributeGroupHeader($attributeGroup)
    {
        $this->getCurrentPage()->clickOnAttributeGroupHeader($attributeGroup);
    }

    /**
     * @When /^I filter attributes with "(.+)"$/
     *
     * @param $filter
     */
    public function iFilterAttributes($filter)
    {
        $this->getCurrentPage()->filterAttributes($filter);
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
        $mainContextSelector = $this->getElementOnCurrentPage('Main context selector');

        $this->spin(function () use ($locale, $mainContextSelector) {
            return $mainContextSelector->getSelectedLocale() === $locale;
        }, sprintf(
            'Expected to have locale "%s", found "%s"',
            $locale,
            $mainContextSelector->getSelectedLocale()
        ));
    }

    /**
     * @param string $locale
     *
     * @When /^I switch the locale to "([^"]*)"$/
     */
    public function iSwitchTheLocaleTo($locale)
    {
        $mainSelector = $this->getElementOnCurrentPage('Main context selector');

        $mainSelector->switchLocale($locale);
        $this->wait();
    }

    /**
     * @param string $scope
     *
     * @When /^I switch the scope to "([^"]*)"$/
     */
    public function iSwitchTheScopeTo($scope)
    {
        $element = $this->getElementOnCurrentPage('Main context selector');

        $element->switchScope(strtolower($scope));
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
        }, sprintf('Expected to see %d items in the locale switcher, saw %d', $expectedLinkCount, $linkCount));

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
                sprintf(
                    'Could not find locale "%s %s" in the locale switcher',
                    $data['locale'],
                    $data['language']
                )
            );
        }
    }

    /**
     * @param string $action open|close
     *
     * @When /^I (open|close) the category tree$/
     */
    public function iToggleTheCategoryTree($action)
    {
        $this->spin(function () use ($action) {
            $thirdColumn = $this->getCurrentPage()->find('css', '.AknDefault-thirdColumnContainer');
            if (null !== $thirdColumn) {
                if (
                    ('open' === $action && $thirdColumn->hasClass('AknDefault-thirdColumnContainer--open')) ||
                    ('close' === $action && !$thirdColumn->hasClass('AknDefault-thirdColumnContainer--open'))
                ) {
                    return true;
                }
            }

            $categorySwitcher = $this->getCurrentPage()->find('css', '.category-switcher');
            if (null !== $categorySwitcher) {
                $categorySwitcher->click();
            }

            return false;
        }, 'Cannot find the category switcher');
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
     * @Given /^I should not see confirm dialog$/
     */
    public function iShouldNotSeeConfirmDialog()
    {
        $dialog = $this->getElementOnCurrentPage('Dialog');

        return $this->spin(function () use ($dialog) {
            return null === $dialog->find('css', '.ok');
        }, 'Confirm dialog button is still visible');
    }

    /**
     * @Given /^I save the (.*)$/
     */
    public function iSave()
    {
        $this->getCurrentPage()->save();
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
        $this->spin(function () use ($attribute, $position) {
            $actual = $this->getCurrentPage()->getAttributePosition($attribute);
            Assert::assertEquals($position, $actual);

            return true;
        }, sprintf('Cannot assert that %s is at position %s', $attribute, $position));
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
     */
    public function theOptionsSectionShouldContainOption($expectedCount = 1)
    {
        $expectedCount = (int) $expectedCount;

        $this->spin(function () use ($expectedCount) {
            return $expectedCount === $this->getCurrentPage()->countOptions();
        }, sprintf('Expecting to see %d option, saw %d.', $expectedCount, $this->getCurrentPage()->countOptions()));
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
        $this->spin(function () use ($title) {
            return $title === $this->getCurrentPage()->getTitle();
        }, sprintf(
            'Expected product title "%s", actually saw "%s"',
            $title,
            $this->getCurrentPage()->getTitle()
        ));
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
     * @param string $inputLabel
     * @param string $expectedValue
     *
     * @Then /^the product ([^"]*) should be empty$/
     * @Then /^the product ([^"]*) should be "([^"]*)"$/
     *
     * @throws \LogicException
     * @throws ExpectationException
     */
    public function theProductFieldValueShouldBe($inputLabel, $expectedValue = '')
    {
        $this->spin(function () use ($inputLabel, $expectedValue) {
            $this->getCurrentPage()->compareFieldValue($inputLabel, $expectedValue);

            return true;
        }, sprintf('Cannot compare product value for "%s" field', $inputLabel));
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
        $page  = $this->getCurrentPage();
        $field = $this->spin(function () use ($page, $label) {
            return $page->findField($label);
        }, sprintf('Field "%s" not found.', $label));

        $this->spin(function () use ($field, $label, $expected) {
            if ($field->hasClass('select2-focusser')) {
                for ($i = 0; $i < 2; ++$i) {
                    $parent = $field->getParent();
                    if (!$parent) {
                        break;
                    }
                    $field = $parent;
                }

                $actual = trim($field->find('css', '.select2-chosen')->getHtml());
            } elseif ($field->hasClass('select2-input')) {
                for ($i = 0; $i < 4; ++$i) {
                    $parent = $field->getParent();
                    if (!$parent) {
                        break;
                    }
                    $field = $parent;
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
                throw new SpinException(
                    sprintf(
                        'Expected product field "%s" to contain "%s", but got "%s".',
                        $label,
                        $expected,
                        $actual
                    )
                );
            }

            return true;
        }, sprintf(
            'Expected product field "%s" to contain "%s".',
            $label,
            $expected
        ));
    }

    /**
     * @param string $not
     * @param string $ordered
     * @param string $choices
     * @param string $label
     *
     * @Then /^I should(?P<not> not)? see the(?P<ordered> ordered)? choices? (?P<choices>.+) in (?P<label>.+)$/
     */
    public function iShouldSeeTheChoicesInField($not, $choices, $label, $ordered = null)
    {
        $this->getCurrentPage()->checkFieldChoices($label, $this->listToArray($choices), !$not, $ordered !== null);
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
     * @param string $label
     * @param string $scope
     *
     * @Then /^the field ([^"]*) should display the ([^"]*) scope label$/
     *
     * @throws \LogicException
     * @throws ExpectationException
     */
    public function theFieldShouldDisplayTheScopeLabel($label, $scope)
    {
        $fieldContainer = $this->getCurrentPage()->findFieldContainer($label);
        $scopeLabel = $fieldContainer->find('css', '.field-scope')->getText();

        if ($scopeLabel !== $scope) {
            throw $this->createExpectationException(
                sprintf(
                    'Scope label %s is not displayed for %s. %s is displayed instead.',
                    $scope,
                    $label,
                    $scopeLabel
                )
            );
        }
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
     * @When /^I change the "(?P<field>[^"]*)" to "(.*)"$/
     * @When /^I change the (?P<language>\w+) (?P<field>\w+) to "(?P<value>[^"]*)"$/
     * @When /^I change the (?P<field>\w+) to an invalid value$/
     */
    public function iChangeTheTo($field, $value = null, $language = null)
    {
        $value = $value !== null ? $value : $this->getInvalidValueFor(
            sprintf('%s.%s', $this->getNavigationContext()->currentPage, $field)
        );

        $this->spin(function () use ($field, $value, $language) {
            if (null !== $language) {
                try {
                    $field = $this->spin(function () use ($field, $language) {
                        return $this->getCurrentPage()->getFieldLocator($field, $this->getLocaleCode($language));
                    }, sprintf('Cannot find "%s" field', $field));
                } catch (\BadMethodCallException $e) {
                    // Use default $field if current page does not provide a getFieldLocator method
                }
            }

            $this->getCurrentPage()->fillField($field, $value);

            return true;
        }, sprintf('Cannot fill the field "%s"', $field));
    }

    /**
     * @Then /^I should see select choices of the "(.*)" in the following order:$/
     *
     * @param string       $fieldName
     * @param PyStringNode $items
     */
    public function iShouldSeeSelectChoicesOrdered($fieldName, PyStringNode $items)
    {
        $expectedChoices = array_values(explode(',', implode(',', $items->getStrings())));

        $label = $this->spin(function () use ($fieldName) {
            return $this->getCurrentPage()->find('css', sprintf('label:contains("%s")', $fieldName));
        }, sprintf('Cannot find field "%s"', $fieldName));

        $this->spin(function () use ($label, $expectedChoices) {
            $fieldContainer = $this->getClosest($label, 'AknFieldContainer');

            $foundChoices = $fieldContainer
                ->findAll('css', '.AknFieldContainer-inputContainer select option');

            $foundChoices = array_map(function ($choice) {
                return trim($choice->getHtml());
            }, $foundChoices);

            $foundChoices = array_values(array_filter($foundChoices));

            return $expectedChoices === $foundChoices;
        }, sprintf(
            'Order of choices for field "%s" is not as expected.',
            $fieldName
        ));
    }

    /**
     * @param $field
     *
     * @When /^I click on the field (?P<field>\w+)$/
     * @When /^I click on the field "(?P<field>[^"]+)"$/
     */
    public function iClickOnTheField($field)
    {
        $field = $this->getCurrentPage()->findField($field);
        $field->click();
    }

    /**
     * @param string $not
     * @param string $attributes
     * @param string $group
     *
     * @Then /^I should (not )?see available attributes? ([^"]*)(?: in group "([^"]*)")?$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeAvailableAttributes($not, $attributes, $group = null)
    {
        $expecting = !$not;

        foreach ($this->listToArray($attributes) as $attribute) {
            $result = $this->getCurrentPage()
                ->getAttributeAddSelect()
                ->hasAvailableOption($attribute, $group);

            if ($expecting !== $result) {
                throw $this->createExpectationException(
                    sprintf(
                        'Expecting to %ssee attribute "%s" under group "%s"',
                        true === (bool) $not ? $not : '',
                        $attribute,
                        $group
                    )
                );
            }
        }
    }

    /**
     * @param string $status 'enabled'|'disabled'
     *
     * @Then /^The available attributes button should be (enabled|disabled)$/
     */
    public function theAvailableAttributeButtonShouldBeEnabled($status)
    {
        $expectedStatus = ('enabled' === $status);

        $this->spin(function () use ($expectedStatus) {
            return $expectedStatus === $this->getCurrentPage()->isAvailableAttributeEnabled();
        }, sprintf('The available attribute button should be %s', $status));
    }

    /**
     * @param string $groups
     *
     * @Then /^I should see available attribute group "([^"]*)"$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeAvailableAttributeGroup($groups)
    {
        foreach ($this->listToArray($groups) as $group) {
            $exists = $this->getCurrentPage()->findAvailableAttributeGroup($group);

            if (true !== $exists) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see attribute group "%s"', $group)
                );
            }
        }
    }

    /**
     * @param string $groups
     *
     * @Then /^I add attributes by groups? "([^"]*)"$/
     */
    public function iAddAttributesByGroup($groups)
    {
        $this->getCurrentPage()
            ->addAttributesByGroup($this->listToArray($groups));
    }

    /**
     * @param string $groups
     *
     * @Then /^the order of groups should be "([^"]*)"$/
     */
    public function orderOfGroupsShouldBe($groups)
    {
        $actualGroups = $this->getCurrentPage()->getGroups();
        Assert::assertEquals($groups, implode($actualGroups, ', '));
    }

    /**
     * @param string $group
     *
     * @Then /^I should see available group "([^"]*)"$/
     *
     * @throws ExpectationException
     */
    public function iShouldSeeAvailableGroup($group)
    {
        foreach ($this->listToArray($group) as $attribute) {
            $element = $this->getCurrentPage()->findAvailableAttributeInGroup($attribute, $group);

            if (null === $element) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see attribute "%s" under group "%s"', $attribute, $group)
                );
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
     * @Given /^I open the family variant creation form$/
     */
    public function iOpenFamilyVariantCreationForm()
    {
        $this->getCurrentPage()->openFamilyVariantCreationForm();
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
            $this->spin(function () use ($attribute, $group) {
                return $this->getCurrentPage()->getAttribute($attribute, $group);
            }, sprintf(
                'Expecting to see attribute %s under group %s, but was not present.',
                $attribute,
                $group
            ));
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
        try {
            $removeLink = $this->getCurrentPage()
                ->getRemoveLinkFor($field);
        } catch (TimeoutException $te) {
            $removeLink = null;
        }

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
     * @throws ElementNotFoundException
     */
    public function iRemoveTheAttribute($field)
    {
        $this->spin(function () use ($field) {
            $removeLink = $this->getCurrentPage()->getRemoveLinkFor($field);

            if (null === $removeLink) {
                throw $this->createExpectationException(
                    sprintf(
                        'Remove link on field "%s" should be displayed.',
                        $field
                    )
                );
            }

            $removeLink->click();

            return true;
        }, 'Cannot click on the remove attribute button');
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
            return $this->getCurrentPage()->find('css', '.modal .ok');
        }, 'Cannot find validate button in attribute modal');

        $addButton->click();

        $this->wait();
    }

    /**
     * @param string $field
     *
     * @Then /^I should not see the add option link for the "([^"]*)" attribute$/
     *
     * @throws ExpectationException
     */
    public function iShouldNotSeeTheAddOptionLinkFor($field)
    {
        if (null !== $this->getCurrentPage()->getAddOptionLinkFor($field)) {
            throw $this->createExpectationException(
                sprintf(
                    'Add option link should not be displayed for attribute "%s".',
                    $field
                )
            );
        }
    }

    /**
     * @Then /^I should see reorder handles$/
     */
    public function iShouldSeeReorderHandles()
    {
        $this->spin(function () {
            $count = $this->getCurrentPage()->countOrderableOptions();

            return $count > 0;
        }, 'Expected to see reorder handles.');
    }

    /**
     * @Then /^I should not see reorder handles$/
     */
    public function iShouldNotSeeReorderHandles()
    {
        $this->spin(function () {
            $count = $this->getCurrentPage()->countOrderableOptions();

            return $count <= 0;
        }, 'Expected not to see reorder handles.');
    }

    /**
     * @Then /^the attribute options order should be (.+)$/
     */
    public function theAttributeOptionsOrderShouldBe($optionCodes)
    {
        $expected = $this->listToArray($optionCodes);

        $this->getCurrentPage()->checkOptionsOrder($expected);
    }

    /**
     * @param string $attributes
     * TODO: use something more generic
     * @Then /^eligible attributes as label should be (.*)$/
     *
     * @throws ExpectationException
     */
    public function eligibleAttributesAsLabelShouldBe($attributes)
    {
        $this->spin(function () use ($attributes) {
            $expectedAttributes = $this->listToArray($attributes);
            $options = $this->getPage('Family edit')->getAttributeAsLabelOptions();

            if (count($expectedAttributes) !== $actual = count($options)) {
                return false;
            }

            if ($expectedAttributes !== $options) {
                return false;
            }

            return true;
        }, sprintf(
                'Expected to see eligible attributes as label %s, actually saw %s',
                json_encode($this->listToArray($attributes)),
                json_encode($this->getPage('Family edit')->getAttributeAsLabelOptions())
            )
        );
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
        $element = null;
        if ($popin) {
            $element = $this->spin(function () {
                return $this->getCurrentPage()->find('css', '.modal, .ui-dialog');
            }, 'Modal not found.');
        }

        foreach ($table->getRowsHash() as $field => $value) {
            $this->spin(function () use ($field, $value, $element) {
                $currentPage = $this->getCurrentPage();

                $currentPage->fillField($field, $value, $element);

                return true;
            }, sprintf('Cannot fill the field %s', $field));
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I fill in the following child information:$/
     */
    public function iFillInTheFollowingChildInformation(TableNode $table)
    {
        $element = $this->spin(function () {
            return $this->getCurrentPage()->find('css', '.modal:not([class^=note-]), .ui-dialog');
        }, 'Modal not found.');

        foreach ($table->getRowsHash() as $field => $value) {
            $this->spin(function () use ($field, $value, $element) {
                $page = $this->getPage('Base form');

                $page->fillField($field, $value, $element);

                return true;
            }, sprintf('Cannot fill the field %s', $field));
        }
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

        return $steps;
    }

    /**
     * @param string $field
     *
     * @Given /^I remove the "([^"]*)" file$/
     */
    public function iRemoveTheFile($field)
    {
        $this->wait();
        $script = sprintf(
            "$('label:contains(\"%s\")').parents('.AknFieldContainer').find('.clear-field').click();",
            $field
        );
        if (!$this->getMainContext()->executeScript($script)) {
            $this->getCurrentPage()->removeFileFromField($field);
        }

        $this->getSession()->executeScript('$(\'.edit .field-input input[type="file"]\').trigger(\'change\');');
        $this->wait();
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
                ->find('css', sprintf('.preview .filename:contains("%s")', $link))
                ->getParent()
                ->find('css', sprintf('.open-media', $link))
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
            $this->spin(function () use ($data) {
                $code = $data['Code'];
                unset($data['Code']);

                $this->getCurrentPage()->addOption($code, $data);

                return true;
            }, sprintf('Unable to create the attribute option %s', $data['Code']));
        }
    }

    /**
     * @param TableNode $table
     *
     * @When /^I edit the following attribute options?:$/
     */
    public function iEditTheFollowingAttributeOptionValue(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $code = $data['Code'];
            unset($data['Code']);

            $this->getCurrentPage()->editOption($code, $data);
            $this->wait();
        }
    }

    /**
     * @When /^I add an empty attribute option$/
     */
    public function iAddAnEmptyAttributeOption()
    {
        $this->getCurrentPage()->createOption();
    }

    /**
     * @When /^I update the last attribute option$/
     */
    public function iUpdateTheLastAttributeOption()
    {
        $this->getCurrentPage()->saveNewOption();
    }

    /**
     * @param string $oldOptionName
     * @param string $newOptionName
     *
     * @Given /^I edit the attribute option "([^"]*)" to turn it to "([^"]*)" and cancel$/
     */
    public function iEditAndCancelToEditTheFollowingAttributeOptions($oldOptionName, $newOptionName)
    {
        $this->spin(function () use ($oldOptionName, $newOptionName) {
            $this->getCurrentPage()->editOptionAndCancel($oldOptionName, $newOptionName);

            return true;
        }, 'Can not edit and cancel code');
    }

    /**
     * $modalWait is a temporary solution waiting for Attributes PEFization
     * TODO Remove the $modalWait parameter after the merge of TIP-732
     *
     * @param string      $button
     * @param string|null $modalWait
     *
     * @Given /^I press the "([^"]*)" button( and wait for modal)?$/
     */
    public function iPressTheButton($button, $modalWait = null)
    {
        $this->spin(function () use ($button, $modalWait) {
            if (null !== $modalWait) {
                foreach ($this->getCurrentPage()->findAll('css', '.modal') as $modal) {
                    if ($modal->isVisible()) {
                        return true;
                    }
                }
            }

            $this->getCurrentPage()->pressButton($button, true);

            return null === $modalWait;
        }, sprintf("Can not find any '%s' button%s", $button, null !== $modalWait ? ' or no modal found' : ''));
    }

    /**
     * @param string $buttonLabel
     *
     * @When /^I press the "([^"]*)" bottom button$/
     */
    public function iPressTheBottomButton($buttonLabel)
    {
        $this->spin(function () use ($buttonLabel) {
            $buttons = $this->getCurrentPage()->findAll('css', '.mass-actions-panel a');
            foreach ($buttons as $button) {
                if ((strtolower(trim($button->getText())) === $buttonLabel ||
                        $button->getAttribute('title') === $buttonLabel
                    ) && $button->isVisible()
                ) {
                    $button->click();

                    return true;
                }
            }

            return false;
        }, sprintf('Can not find any bottom button "%s"', $buttonLabel));
    }

    /**
     * @param string $locator
     *
     * @When /^I hover over the element "([^"]*)"$/
     */
    public function iHoverOverTheElement($locator)
    {
        $page = $this->getCurrentPage();
        $element = $this->spin(function () use ($page, $locator) {
            return $page->find('css', $locator);
        }, sprintf("Can not find any '%s' element", $locator));

        $element->mouseOver();
    }

    /**
     * @param string $button
     *
     * @Given /^I should see the "([^"]*)" button$/
     */
    public function iShouldSeeTheButton($button)
    {
        $this->getMainContext()->spin(function () use ($button) {
            return $this->getCurrentPage()->getButton($button);
        }, sprintf("Can not find any '%s' button", $button));
    }

    /**
     * @param string $button
     *
     * @throws TimeoutException
     *
     * @Given /^The button "([^"]*)" should be disabled$/
     */
    public function theButtonShouldBeDisabled($button)
    {
        $buttonNode = $this->spin(function () use ($button) {
            return $this->getCurrentPage()->getButton($button);
        }, sprintf("Can not find any '%s' button", $button));

        $this->spin(function () use ($buttonNode) {
            return $buttonNode->hasClass('disabled') || $buttonNode->hasClass('AknButton--disabled');
        }, sprintf("The button '%s' is not disabled", $button));
    }

    /**
     * @param string $button
     *
     * @throws TimeoutException
     *
     * @Given /^The button "([^"]*)" should be enabled$/
     */
    public function theButtonShouldBeEnabled($button)
    {
        $buttonNode = $this->spin(function () use ($button) {
            return $this->getCurrentPage()->getButton($button);
        }, sprintf("Can not find any '%s' button", $button));

        $this->spin(function () use ($buttonNode) {
            return !$buttonNode->hasClass('disabled');
        }, sprintf("The button '%s' is not enabled", $button));
    }

    /**
     * @param string $button
     *
     * @Given /^I should not see the "([^"]*)" button$/
     */
    public function iShouldNotSeeTheButton($button)
    {
        $this->spin(function () use ($button) {
            return null === $this->getCurrentPage()->getButton($button);
        }, sprintf('Button "%s" should not be displayed', $button));
    }

    /**
     * @param string $button
     *
     * @Given /^I should not see the "([^"]*)" icon button$/
     */
    public function iShouldNotSeeTheIconButton($button)
    {
        $this->spin(function () use ($button) {
            return null === $this->getCurrentPage()->getIconButton($button);
        }, sprintf('Icon button "%s" should not be displayed', $button));
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
                ->find('css', sprintf('.ui-dialog button:contains("%1$s"), .modal a:contains("%1$s"), .modal button:contains("%1$s"), .modal .AknButton:contains("%1$s")', $buttonLabel));
        }, sprintf('Cannot find "%s" button label in modal', $buttonLabel));

        $buttonElement->press();

        $this->wait();
    }

    /**
     * @param string $buttonLabel
     *
     * @Given /^I press the cancel button in the popin$/
     */
    public function iPressTheCancelButtonInThePopin()
    {
        $buttonElement = $this->spin(function () {
            return $this
                ->getCurrentPage()
                ->find('css', '.modal-full-body .AknButtonList > .AknFullPage-cancel');
        }, 'Cannot find cancel button label in modal');

        $buttonElement->click();

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
        $this->spin(function () use ($item, $button) {
            $this
                ->getCurrentPage()
                ->getDropdownButtonItem($item, $button)
                ->click();

            return true;
        }, sprintf('Cannot click on item "%s" on the dropdown "%s"', $item, $button));

        $this->wait();
    }

    /**
     * @param string $item
     * @param string $button
     *
     * @Given /^I should see "([^"]*)" on the "([^"]*)" dropdown button$/
     */
    public function iShouldSeeOnTheDropdownButton($item, $button)
    {
        $this->spin(function () use ($item, $button) {
            return null !== $this->getCurrentPage()->getDropdownButtonItem($item, $button);
        }, sprintf('Cannot find item "%s" on the dropdown "%s"', $item, $button));

        $this->getCurrentPage()->find('css', 'body')->click();
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
        $this->getCurrentPage()->configure();
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
    }

    /**
     * @param string $status
     *
     * @When /^I (en|dis)able the inclusion of sub-categories$/
     */
    public function iSwitchTheSubCategoriesInclusion($status)
    {
        $this->spin(function () use ($status) {
            $switch = $this->getCurrentPage()->findById('nested_switch_input');

            if (('en' === $status) !== $switch->isChecked()) {
                $switch->getParent()->find('css', 'label')->click();
            }

            return true;
        }, sprintf('Cannot %sable the inclusion of sub-categories', $status));
    }

    /**
     * @param ProductInterface $product
     *
     * @Given /^(product "([^"]*)") should be disabled$/
     *
     * @throws ExpectationException
     */
    public function productShouldBeDisabled(ProductInterface $product)
    {
        $this->spin(function () use ($product) {
            $this->getMainContext()->getEntityManager()->refresh($product);

            return !$product->isEnabled();
        }, 'Product was expected to be be disabled');
    }

    /**
     * @param ProductInterface $product
     *
     * @Given /^(product "([^"]*)") should be enabled$/
     *
     * @throws ExpectationException
     */
    public function productShouldBeEnabled(ProductInterface $product)
    {
        $this->spin(function () use ($product) {
            $this->getMainContext()->getEntityManager()->refresh($product);

            return $product->isEnabled();
        }, 'Product was expected to be be enabled');
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
        $this->spin(function () use ($sku, $expectedFamily) {
            $product      = $this->getFixturesContext()->getProduct($sku);
            $actualFamily = $product->getFamily() ? $product->getFamily()->getCode() : '';

            return $expectedFamily === $actualFamily;
        }, sprintf('Expecting the family of "%s" to be "%s".', $sku, $expectedFamily));
    }

    /**
     * @param string      $code
     * @param string|null $expectedFamily
     *
     * @Then /^the product model "([^"]*)" should have no family$/
     * @Then /^the family of (?:the )?product model "([^"]*)" should be "([^"]*)"$/
     */
    public function theFamilyOfProductModelShouldBe($code, $expectedFamily = '')
    {
        $this->spin(function () use ($code, $expectedFamily) {
            $productModel = $this->getFixturesContext()->getProductModel($code);
            $actualFamily = $productModel->getFamily() ? $productModel->getFamily()->getCode() : '';

            return $expectedFamily === $actualFamily;
        }, sprintf('Expecting the family of "%s" to be "%s".', $code, $expectedFamily));
    }

    /**
     * @param string $sku
     *
     * @Then /^the product "([^"]*)" should not have any category$/
     */
    public function theProductShouldNotHaveAnyCategory($sku)
    {
        $product = $this->getFixturesContext()->getProduct($sku);
        Assert::assertEmpty($product->getCategoryCodes());
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
        $this->spin(function () use ($count) {
            return (int) $count === count($this->getCurrentPage()->getHistoryRows());
        }, sprintf('Expected %d updates, saw %d.', $count, count($this->getCurrentPage()->getHistoryRows())));
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
        $this->spin(function () use ($count) {
            return $this->getCurrentPage()->find('css', sprintf('.AknBadge:contains("%d")', $count));
        }, sprintf('Can not find any badge with count "%s"', $count));
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
     *
     * @throws \Exception
     */
    public function iWaitForTheJobToFinish($code)
    {
        $this->wait();

        $this->spin(function () use ($code) {
            $jobInstance = $this->getFixturesContext()->getJobInstance($code);
            // Force to retrieve its job executions
            $jobInstance->getJobExecutions()->setInitialized(false);

            $this->getFixturesContext()->refresh($jobInstance);

            $jobExecution = $jobInstance->getJobExecutions()->last();
            $this->getFixturesContext()->refresh($jobExecution);

            return $jobExecution && !$jobExecution->isRunning();
        }, sprintf('The job execution of "%s" was too long', $code));

        $this->getMainContext()->getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();

        return [
            new Step\Then(sprintf('I go on the last executed job resume of "%s"', $code))
        ];
    }

    /**
     * @Given /^I wait for the "([^"]*)" quick export to finish$/
     */
    public function iWaitForTheQuickExportToFinish($code)
    {
        $this->waitForMassEditJobToFinish($code);
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
            Assert::assertSame(0, strpos($row, $category), sprintf('Expecting category "%s", saw "%s"', $category, $row));
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
        return $this->spin(function () {
            return $this->getPage('Product edit')->getImagePreview();
        }, 'Image preview could not be displayed.');
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
        $expectation = $not === '';
        foreach ($this->listToArray($channels) as $channel) {
            $this->spin(function () use ($attribute, $channel, $expectation) {
                return $expectation === $this->getCurrentPage()->isAttributeRequired($attribute, $channel);
            }, sprintf(
                'Attribute %s should be%s required in channel %s',
                $attribute,
                $not,
                $channel
            ));
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
     * @param string $channel
     * @param string $ratio
     *
     * @Given /^completeness of "([^"]*)" should be "([^"]*)"$/
     */
    public function completenessOfShouldBe($channel, $ratio)
    {
        $actual = $this->getCurrentPage()->getChannelCompleteness($channel);
        Assert::assertEquals(
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
        Assert::assertEquals(
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
     * @param string $group
     *
     * @Then /^I should be on the "([^"]*)" attribute group$/
     */
    public function iShouldBeOnTheAttributeGroup($group)
    {
        $groupNode = $this->getCurrentPage()->getAttributeGroupTab($group);

        Assert::assertTrue(
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
        sleep($seconds);
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
            ->choose();

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
        $this->iMoveToTheConfirmPage();
    }

    /**
     * @Given /^I click on the cancel button of the mass edit$/
     */
    public function iCancelTheMassEdit()
    {
        $this->getCurrentPage()->cancel();
    }

    /**
     * @Given /^I move on to the choose step$/
     */
    public function iMoveOnToTheChooseStep()
    {
        $this->scrollContainerTo(900);
        $this->getCurrentPage()->select();
    }

    /**
     * @Given /^I validate mass edit$/
     */
    public function iValidateTheMassEdit()
    {
        $this->scrollContainerTo(900);
        $this->getCurrentPage()->confirm();
    }

    /**
     * @Given /^I confirm mass edit$/
     */
    public function iConfirmTheMassEdit()
    {
        $this->iMoveToTheConfirmPage();
        $this->scrollContainerTo(900);
        $this->getCurrentPage()->confirm();
    }

    /**
     * @Given /^I move to the confirm page$/
     */
    public function iMoveToTheConfirmPage()
    {
        $this->scrollContainerTo(900);
        $this->getCurrentPage()->configure();
    }

    /**
     * @Then /^I click on the Akeneo logo$/
     */
    public function iClickOnTheAkeneoLogo()
    {
        $this->getCurrentPage()->clickOnAkeneoLogo();
    }

    /**
     * @param string $language
     *
     * @Given /^I select (.+) (?:language|locale)$/
     */
    public function iSelectLanguage($language)
    {
        $this->spin(function () use ($language) {
            $this->getCurrentPage()->selectFieldOption('system-locale', $language);

            return true;
        }, 'System locale field was not found');
    }

    /**
     * @param string|null $not
     * @param string      $locale
     *
     * @Then /^I should (not )?see (.+) locale option$/
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function iShouldSeeLocaleOption($not, $locale)
    {
        $selectNames = ['system-locale', 'pim_user_user_form[uiLocale]'];
        $field = null;
        foreach ($selectNames as $selectName) {
            try {
                $field = (null !== $field) ? $field : $this->getCurrentPage()->findField($selectName);
            } catch (TimeoutException $e) {
                // We didn't find the system locale or user locale
            }
        }
        if (null === $field) {
            throw new \Exception(sprintf('Could not find field with name %s', json_encode($selectNames)));
        }

        $options = $field->findAll('css', 'option');

        foreach ($options as $option) {
            $text = $option->getHtml();
            if ($text === $locale) {
                if ($not) {
                    throw new \Exception(sprintf('Should not see %s locale', $locale));
                } else {
                    return true;
                }
            }
        }

        return true;
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
            $this->getCurrentPage()->findFieldInTabSection($groupField, $data[0]);
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
        $this->getSession()->executeScript(
            sprintf('$(".scrollable-container, .AknDefault-mainContent").scrollTop(%d);', $y)
        );
    }

    /**
     * @param TableNode $table
     *
     * @throws ExpectationException
     *
     * @return array
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

        Assert::assertTrue(in_array($contentType, $headers['content-type']));
    }

    /**
     * @param string $family
     *
     * @Then /^I change the family of the product to "([^"]*)"$/
     */
    public function iChangeTheFamilyOfTheProductTo($family)
    {
        $this->spin(function () use ($family) {
            $this->getCurrentPage()->changeFamily($family);

            return true;
        }, sprintf('Cannot change the product family to %s', $family));
    }

    /**
     * Clear the Unit of Work
     */
    public function clearUOW()
    {
        $this->getMainContext()->getEntityManager()->clear();
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
     * @param string $condition
     */
    protected function wait($condition = null)
    {
        $this->getMainContext()->wait($condition);
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
    public function replacePlaceholders($value)
    {
        return $this->getMainContext()->getSubcontext('fixtures')->replacePlaceholders($value);
    }

    /**
     * @param $code
     */
    protected function waitForMassEditJobToFinish($code)
    {
        $jobExecution = $this->spin(function () use ($code) {
            $jobInstance = $this->getFixturesContext()->getJobInstance($code);
            // Force to retrieve its job executions
            $jobInstance->getJobExecutions()->setInitialized(false);

            return $jobInstance->getJobExecutions()->last();
        }, sprintf('No job execution found for job with code "%s"', $code));

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
     * Check the number of items in a select2 autocomplete. This function spins when autocomplete is searching; it
     * returns 0 only if special dom item is found.
     *
     * @param string $expectedCount
     *
     * @Then /^I should see (\d+) items? in the autocomplete$/
     */
    public function iShouldSeeAutocompleteItems($expectedCount)
    {
        $items = $this->spin(function () {
            return $this
                ->getCurrentPage()
                ->findAll('css', '.select2-results .select2-result-selectable, .select2-results .select2-no-results');
        }, 'Cannot find any select2 items');

        if ($items[0]->hasClass('select2-no-results')) {
            Assert::assertEquals((int) $expectedCount, 0);
        } else {
            Assert::assertEquals((int) $expectedCount, count($items));
        }
    }

    /**
     * @When /^I collapse the column$/
     */
    public function iCollapseTheColumn()
    {
        $this->spin(function () {
            $collapseButtons = $this->getCurrentPage()->findAll('css', '.AknColumn-collapseButton');

            foreach ($collapseButtons as $collapseButton) {
                if ($collapseButton->isVisible()) {
                    $collapseButton->click();
                }
            }

            return null !== $this->getCurrentPage()->find('css', '.AknColumn--collapsed');
        }, 'Could not collapse the column');
    }

    /**
     * @When /^I uncollapse the column$/
     */
    public function iUncollapseTheColumn()
    {
        $this->spin(function () {
            $collapseButtons = $this->getCurrentPage()->findAll('css', '.AknColumn-collapseButton');

            foreach ($collapseButtons as $collapseButton) {
                if ($collapseButton->isVisible()) {
                    $collapseButton->click();
                }
            }

            return null === $this->getCurrentPage()->find('css', '.AknColumn--collapsed');
        }, 'Could not uncollapse the column');
    }

    /**
     * @param NodeElement $tab
     *
     * @return integer
     */
    protected function getTabErrorsCount($tab)
    {
        $badge = $tab->find('css', '.invalid-badge');

        return (null === $badge) ? 0 : intval($badge->getText());
    }
}
