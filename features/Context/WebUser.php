<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Context\Step;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Product;

/**
 * Context of the website
 *
 * @author    Gildas Quéméner <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebUser extends RawMinkContext
{
    private $windowWidth;

    private $windowHeight;

    /**
     * Constructor
     *
     * @param integer $windowWidth
     * @param integer $windowHeight
     */
    public function __construct($windowWidth, $windowHeight)
    {
        $this->windowWidth  = $windowWidth;
        $this->windowHeight = $windowHeight;
    }
    /* -------------------- Page-related methods -------------------- */

    /**
     * @BeforeScenario
     */
    public function maximize()
    {
        try {
            $this->getSession()->resizeWindow($this->windowWidth, $this->windowHeight);
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @BeforeScenario
     */
    public function clearRecordedMails()
    {
        $this->getMailRecorder()->clear();
    }

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
     * @Given /^I create a new (\w+)$/
     */
    public function iCreateANew($entity)
    {
        $entity = ucfirst($entity);
        $this->getPage(sprintf('%s index', $entity))->clickCreationLink();
        $this->getNavigationContext()->currentPage = sprintf('%s creation', $entity);
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
            $this->getSession()->executeScript(
                sprintf("require(['oro/navigation'], function(Nav) { Nav.getInstance().setLocation('%s'); } );", $url)
            );
            $this->wait();

            $currentUrl = $this->getSession()->getCurrentUrl();
            $currentUrl = explode('#url=', $currentUrl);
            $currentUrl = end($currentUrl);

            assertTrue(
                (bool) (($url === $currentUrl) || ($url ."|g/" === $currentUrl)),
                sprintf('Error ocurred on page "%s"', $data['page'])
            );

            $loadedCorrectly = (bool) $this->getSession()->evaluateScript('return $(\'img[alt="Akeneo"]\').length;');
            assertTrue($loadedCorrectly, sprintf('Javascript error ocurred on page "%s"', $data['page']));
        }
    }

    /**
     * @param string $title
     *
     * @Then /^I should see the title "([^"]*)"$/
     */
    public function iShouldSeeTheTitle($title)
    {
        $this->getCurrentPage()->checkHeadTitle($title);
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
     */
    public function iShouldSeeTheCategoryUnderTheCategory($not, $child, $parent)
    {
        $this->wait(); // Make sure that the tree is loaded
        $not = ($not !== '') ? true : false;

        $parentNode = $this->getCurrentPage()->findCategoryInTree($parent);
        $childNode = $parentNode->getParent()->find('css', sprintf('li a:contains(%s)', $child));

        if (($not && $childNode) || (!$not && !$childNode)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see category "%s" under the category "%s", not found',
                    $child,
                    $parent
                )
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
        $this->getCurrentPage()->visitTab($tab);
    }

    /* -------------------- Other methods -------------------- */

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
     * @param string $deactivated
     * @param string $currencies
     *
     * @Then /^I should see (de)?activated currency (.*)$/
     * @Then /^I should see (de)?activated currencies (.*)$/
     */
    public function iShouldSeeActivatedCurrencies($deactivated, $currencies)
    {
        $currencies = $this->listToArray($currencies);

        foreach ($currencies as $currency) {
            if ($deactivated) {
                if (!$this->getPage('Currency index')->findDeactivatedCurrency($currency)) {
                    throw $this->createExpectationException(sprintf('Currency "%s" is not deactivated.', $currency));
                }
            } else {
                if (!$this->getPage('Currency index')->findActivatedCurrency($currency)) {
                    throw $this->createExpectationException(sprintf('Currency "%s" is not activated.', $currency));
                }
            }
        }
    }

    /**
     * @param string $currencies
     *
     * @When /^I activate the (.*) currency$/
     */
    public function iActivateTheCurrency($currencies)
    {
        $this->getPage('Currency index')->activateCurrencies($this->listToArray($currencies));
        $this->wait();
    }

    /**
     * @param string $currencies
     *
     * @When /^I deactivate the (.*) currency$/
     */
    public function iDeactivateTheCurrency($currencies)
    {
        $this->getPage('Currency index')->deactivateCurrencies($this->listToArray($currencies));
        $this->wait();
    }

    /**
     * @param string $deactivated
     * @param string $locales
     *
     * @throws ExpectationException
     *
     * @When /^I should see (de)?activated locales? (.*)$/
     */
    public function iShouldSeeActivatedLocales($deactivated, $locales)
    {
        $locales = $this->listToArray($locales);

        foreach ($locales as $locale) {
            if ($deactivated) {
                if (!$this->getPage('Locale index')->findDeactivatedLocale($locale)) {
                    throw $this->createExpectationException(
                        sprintf('Locale "%s" is not deactivated', $locale)
                    );
                }
            } else {
                if (!$this->getPage('Locale index')->findActivatedLocale($locale)) {
                    throw $this->createExpectationException(
                        sprintf('Locale "%s" is not activated', $locale)
                    );
                }
            }
        }
    }

    /**
     * @param string $locale
     *
     * @When /^I switch the locale to "([^"]*)"$/
     */
    public function iSwitchTheLocaleTo($locale)
    {
        $this->getCurrentPage()->switchLocale($locale);
        $this->wait();
    }

    /**
     * @param TableNode $table
     *
     * @Then /^the locale switcher should contain the following items:$/
     */
    public function theLocaleSwitcherShouldContainTheFollowingItems(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            if (!$this->getPage('Product edit')->findLocaleLink($data['language'], $data['label'])) {
                throw $this->createExpectationException(
                    sprintf(
                        'Could not find locale "%s %s" in the locale switcher',
                        $data['locale'],
                        $data['label']
                    )
                );
            }
        }
    }

    /**
     * @param string $action
     * @param string $identifier
     * @param string $entityType
     *
     * @Given /^I try to ([^"]*) "([^"]*)" from the ([^"]*) grid$/
     */
    public function iTryToDoActionFromTheGrid($action, $identifier, $entityType)
    {
        $entityType = ucfirst(strtolower($entityType));
        $entityPage = $entityType.' index';

        $page = $this->getPage($entityPage);
        if (!$page) {
            throw $this->createExpectationException(sprintf('Unable to find page "%s"', $entityPage));
        }

        $action = ucfirst(strtolower($action));

        $page->clickOnAction($identifier, $action);
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
        $this->wait();
    }

    /**
     * @param string  $attribute
     * @param integer $position
     *
     * @Given /^I change the attribute "([^"]*)" position to (\d+)$/
     */
    public function iChangeTheAttributePositionTo($attribute, $position)
    {
        $this->getCurrentPage()->dragAttributeToPosition($attribute, $position)->save();
        $this->wait();
    }

    /**
     * @param string  $attribute
     * @param integer $position
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
     */
    public function iShouldSeeTheSection($title)
    {
        if (!$this->getCurrentPage()->getSection($title)) {
            throw $this->createExpectationException(sprintf('Expecting to see the %s section.', $title));
        }
    }

    /**
     * @Given /^the Options section should contain ([^"]*) option$/
     */
    public function theOptionsSectionShouldContainOption()
    {
        if (1 !== $count = $this->getCurrentPage()->countOptions()) {
            throw $this->createExpectationException(sprintf('Expecting to see the 1 option, saw %d.', $count));
        }
    }

    /**
     * @Then /^the option should not be removable$/
     */
    public function theOptionShouldNotBeRemovable()
    {
        if (0 !== $this->getCurrentPage()->countRemovableOptions()) {
            throw $this->createExpectationException('The option should not be removable.');
        }
    }

    /**
     * @param string $group
     * @param string $attributes
     *
     * @return void
     * @Given /^attributes? in group "([^"]*)" should be (.*)$/
     */
    public function attributesInGroupShouldBe($group, $attributes)
    {
        $page       = $this->getCurrentPage();
        $attributes = $this->listToArray($attributes);
        $page->visitGroup($group);

        $group = $this->getFixturesContext()->getAttributeGroup($group) ?: AttributeGroup::DEFAULT_GROUP_CODE;

        if (count($attributes) !== $actual = $page->getFieldsCountFor($group)) {
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
            $page->getFieldsForGroup($group)
        );

        if (count(array_diff($attributes, $labels))) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see attributes "%s" in group "%s", but saw "%s".',
                    join('", "', $attributes),
                    $group,
                    join('", "', $labels)
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
     * @param string $expected
     *
     * @Then /^the product (.*) should be empty$/
     * @Then /^the product (.*) should be "([^"]*)"$/
     */
    public function theProductFieldValueShouldBe($fieldName, $expected = '')
    {
        $actual = $this->getCurrentPage()->findField($fieldName)->getValue();

        if ($expected !== $actual) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected product %s to be "%s", but got "%s".',
                    $fieldName,
                    $expected,
                    $actual
                )
            );
        }
    }

    /**
     * @param string $field
     * @param string $value
     * @param string $language
     *
     * @return void
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

        $value = $value ?: $this->getInvalidValueFor(
            sprintf('%s.%s', $this->getNavigationContext()->currentPage, $field)
        );

        return $this->getCurrentPage()->fillField($field, $value);
    }

    /**
     * @param string $not
     * @param string $attributes
     * @param string $group
     *
     * @Then /^I should (not )?see available attributes? (.*) in group "([^"]*)"$/
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
     */
    public function iShouldSeeTheFamilies($families)
    {
        $expectedFamilies = $this->listToArray($families);

        if ($expectedFamilies !== $families = $this->getPage('Family index')->getFamilies()) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting to see families %s, but saw %s',
                    print_r($expectedFamilies, true),
                    print_r($families, true)
                )
            );
        }
    }

    /**
     * @param string $attribute
     * @param string $group
     *
     * @Given /^I should see attribute "([^"]*)" in group "([^"]*)"$/
     */
    public function iShouldSeeAttributeInGroup($attribute, $group)
    {
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

    /**
     * @param string $not
     * @param string $field
     *
     * @Then /^I should (not )?see a remove link next to the "([^"]*)" field$/
     */
    public function iShouldSeeARemoveLinkNextToTheField($not, $field)
    {
        $removeLink = $this->getPage('Product edit')->getRemoveLinkFor($field);
        if (!$not) {
            if (!$removeLink) {
                throw $this->createExpectationException(
                    sprintf(
                        'Remove link on field "%s" should not be displayed.',
                        $field
                    )
                );
            }
        } else {
            if ($removeLink) {
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
     * @param string $field
     *
     * @When /^I remove the "([^"]*)" attribute$/
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
        $this->getSession()->getPage()->clickLink('OK');
        $this->wait();
    }

    /**
     * @param string $attributes
     *
     * @Then /^eligible attributes as label should be (.*)$/
     */
    public function eligibleAttributesAsLabelShouldBe($attributes)
    {
        $expectedAttributes = $this->listToArray($attributes);
        $options = $this->getPage('Family edit')->getAttributeAsLabelOptions();

        if (count($expectedAttributes) !== $actual = count($options)) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected to see %d eligible attributes as label, actually saw %d:'."\n%s",
                    count($expectedAttributes),
                    $actual,
                    print_r($options, true)
                )
            );
        }

        if ($expectedAttributes !== $options) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected to see eligible attributes as label %s, actually saw %s',
                    print_r($expectedAttributes, true),
                    print_r($options, true)
                )
            );
        }
    }

    /**
     * @param string $attribute
     *
     * @Given /^I choose "([^"]*)" as the label of the family$/
     */
    public function iChooseAsTheLabelOfTheFamily($attribute)
    {
        $this->getPage('Family edit')->selectAttributeAsLabel($attribute)->save();
    }

    /**
     * @param string $type
     *
     * @When /^I select the attribute type "([^"]*)"$/
     */
    public function iSelectTheAttributeType($type)
    {
        $this->getPage('Attribute creation')->selectAttributeType($type);

        $this->wait();
    }

    /**
     * @param string $channel
     *
     * @Given /^I select the channel "([^"]*)"$/
     */
    public function iSelectChannel($channel)
    {
        $this->getPage('Export creation')->selectChannel($channel);
    }

    /**
     * @param string $locale
     *
     * @Given /^I select the locale "([^"]*)"$/
     */
    public function iSelectLocale($locale)
    {
        $this->getPage('Channel creation')->selectLocale($locale);
    }

    /**
     * @param string $currency
     *
     * @Given /^I select the currency "([^"]*)"$/
     */
    public function iSelectCurrency($currency)
    {
        $this->getPage('Channel creation')->selectCurrency($currency);
    }

    /**
     * @param string $status
     *
     * @Given /^I select the status "([^"]*)"$/
     */
    public function iSelectStatus($status)
    {
        $this->getPage('User creation')->selectStatus($status);
    }

    /**
     * @param string $owner
     *
     * @Given /^I select the owner "([^"]*)"$/
     */
    public function iSelectOwner($owner)
    {
        $this->getPage('User creation')->selectOwner($owner);
    }

    /**
     * @param string $role
     *
     * @Given /^I select the role "([^"]*)"$/
     */
    public function iSelectRole($role)
    {
        $this->scrollContainerTo(600);
        $this->getPage('User creation')->selectRole($role);
    }

    /**
     * @param string $fields
     *
     * @Then /^I should see the (.*) fields?$/
     */
    public function iShouldSeeTheFields($fields)
    {
        $fields = $this->listToArray($fields);
        foreach ($fields as $field) {
            if (!$this->getCurrentPage()->findField($field)) {
                throw $this->createExpectationException(sprintf('Expecting to see field "%s".', $field));
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
        $fields = $this->listToArray($fields);
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
     * @param TableNode $table
     *
     * @Given /^I fill in the following information:$/
     */
    public function iFillInTheFollowingInformation(TableNode $table)
    {
        foreach ($table->getRowsHash() as $field => $value) {
            $this->getCurrentPage()->fillField($field, $value);
        }
    }

    /**
     * @param string $file
     * @param string $field
     *
     * @Given /^I attach file "([^"]*)" to "([^"]*)"$/
     */
    public function attachFileToField($file, $field)
    {
        if ($this->getMinkParameter('files_path')) {
            $fullPath = rtrim(realpath($this->getMinkParameter('files_path')), DIRECTORY_SEPARATOR)
                .DIRECTORY_SEPARATOR.$file;
            if (is_file($fullPath)) {
                $file = $fullPath;
            }
        }

        $this->getCurrentPage()->attachFileToField($field, $file);

        try {
            $this->getSession()->executeScript('$("[disabled]").removeAttr("disabled");');
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @param string $field
     *
     * @Given /^I remove the "([^"]*)" file$/
     */
    public function iRemoveTheFile($field)
    {
        try {
            $this->getSession()->executeScript(
                "$('label:contains(\"{$field}\")').parent().find('.remove-upload').click();"
            );
        } catch (UnsupportedDriverActionException $e) {
            $this->getCurrentPage()->removeFileFromField($field);
        }
    }

    /**
     * @param string $link
     *
     * @return Step\Given
     * @Given /^I open "([^"]*)" in the current window$/
     */
    public function iOpenInTheCurrentWindow($link)
    {
        try {
            $this->getSession()->executeScript(
                "$('[target]').removeAttr('target');"
            );

            return new Step\Given(sprintf('I follow "%s"', $link));
        } catch (UnsupportedDriverActionException $e) {
            throw $this->createExpectationException('You must use selenium for this feature.');
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attribute types should have the following fields$/
     */
    public function theFollowingAttributeTypesShouldHaveTheFollowingFields(TableNode $table)
    {
        foreach ($table->getRowsHash() as $type => $fields) {
            $this->iSelectTheAttributeType($type);
            try {
                $this->iShouldSeeTheFields($fields);
            } catch (ExpectationException $e) {
                throw $this->createExpectationException(sprintf('%s: %s', $type, $e->getMessage()));
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^I create the following attribute options:$/
     */
    public function iCreateTheFollowingAttributeOptions(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $this->getCurrentPage()->addOption($data['Code'], $data['Selected by default']);
        }
    }

    /**
     * @param string $button
     *
     * @Given /^I press the "([^"]*)" button$/
     */
    public function iPressTheButton($button)
    {
        $this->getCurrentPage()->pressButton($button);
        $this->wait();
    }

    /**
     * @Given /^I disable the product$/
     */
    public function iDisableTheProduct()
    {
        $this->getPage('Product edit')->disableProduct()->save();
        $this->wait();
    }

    /**
     * @Given /^I disable the products$/
     */
    public function iDisableTheProducts()
    {
        $this->getPage('Batch ChangeStatus')->disableProducts()->next();
        $this->getPage('Batch ChangeStatus')->confirm();
        $this->wait();
    }

    /**
     * @Given /^I enable the product$/
     */
    public function iEnableTheProduct()
    {
        $this->getPage('Product edit')->enableProduct()->save();
        $this->wait();
    }

    /**
     * @Given /^I enable the products$/
     */
    public function iEnableTheProducts()
    {
        $this->getPage('Batch ChangeStatus')->enableProducts()->next();
        $this->getPage('Batch ChangeStatus')->confirm();
        $this->wait();
    }

    /**
     * @param Product $product
     *
     * @Given /^(product "([^"]*)") should be disabled$/
     */
    public function productShouldBeDisabled(Product $product)
    {
        $this->getMainContext()->getEntityManager()->refresh($product);
        if ($product->isEnabled()) {
            throw $this->createExpectationException('Product was expected to be be disabled');
        }
    }

    /**
     * @param Product $product
     *
     * @Given /^(product "([^"]*)") should be enabled$/
     */
    public function productShouldBeEnabled(Product $product)
    {
        $this->getMainContext()->getEntityManager()->refresh($product);
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
        $product = $this->getFixturesContext()->getProduct($sku);
        $this->getMainContext()->getEntityManager()->refresh($product);

        $actualFamily = $product->getFamily() ? $product->getFamily()->getCode() : '';
        assertEquals(
            $expectedFamily,
            $actualFamily,
            sprintf('Expecting the family of "%s" to be "%s", not "%s".', $sku, $expectedFamily, $actualFamily)
        );
    }

    /**
     * @param string $channel
     * @param string $not
     * @param string $category
     *
     * @Given /^the channel (.*) is (not )?able to export category (.*)$/
     */
    public function theChannelIsAbleToExportCategory($channel, $not, $category)
    {
        $expected = (bool) $not;
        $actual = $this->getPage('Channel index')->channelCanExport($channel, $category);

        if ($expected !== $actual) {
            throw $this->createExpectationException(
                sprintf(
                    'Expecting channel %s %sto be able to export category %s',
                    $channel,
                    $not,
                    $category
                )
            );
        }
    }

    /**
     * @param integer $count
     *
     * @Then /^there should be (\d+) update$/
     * @Then /^there should be (\d+) updates$/
     */
    public function thereShouldBeUpdate($count)
    {
        if ((int) $count !== $countUpdates = $this->getPage('Product edit')->countUpdates()) {
            throw $this->createExpectationException(sprintf('Expected %d updates, saw %d.', $count, $countUpdates));
        }
    }

    /**
     * @param string $product
     * @param string $data
     *
     * @Then /^I should see product "([^"]*)" with data (.*)$/
     */
    public function iShouldSeeProductWithData($product, $data)
    {
        $row = $this->getPage('Product index')->getRow($product);
        $data = $this->listToArray($data);

        if (!$row) {
            throw $this->createExpectationException(sprintf('Expecting to see product %s, not found', $product));
        }

        $rowHtml = $row->getHtml();
        foreach ($data as $cellData) {
            if (strpos($rowHtml, $cellData) === false) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see product data %s, not found', $cellData)
                );
            }
        }
    }

    /**
     * @param TableNode $table
     *
     * @Then /^I should see history:$/
     */
    public function iShouldSeeHistoryWithData(TableNode $table)
    {
        $expectedUpdates = $table->getHash();
        $rows = $this->getCurrentPage()->getHistoryRows();
        foreach ($expectedUpdates as $updateRow) {
            $isPresent = false;
            foreach ($rows as $row) {
                $rowStr       = str_replace(array(' ', "\n"), '', strip_tags(nl2br($row->getHtml())));
                $actionFound  = (strpos($rowStr, $updateRow['action']) !== false);
                $versionFound = (strpos($rowStr, $updateRow['version']) !== false);
                $dataFound    = (strpos($rowStr, $updateRow['data']) !== false);
                if ($actionFound and $versionFound and $dataFound) {
                    $isPresent = true;
                    break;
                }
            }
            if (!$isPresent) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see history data %s, not found', implode(', ', $updateRow))
                );
            }
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
            $category->click();
            $this->wait();
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
     * @Given /^I blur (.*)$/
     */
    public function iBlur()
    {
        $this->getCurrentPage()->find('css', 'body')->click();
        $this->wait();
        $this->wait();
    }

    /**
     * @param string $exportTitle
     *
     * @Given /^I create a new "([^"]*)" export$/
     */
    public function iCreateANewExport($exportTitle)
    {
        $this->getPage('Export index')->clickExportCreationLink($exportTitle);
        $this->wait();
        $this->getNavigationContext()->currentPage = 'Export creation';
    }

    /**
     * @Given /^I try to create an unknown export$/
     */
    public function iTryToCreateAnUnknownExport()
    {
        $this->openPage('Export creation');
    }

    /**
     * @param string $importTitle
     *
     * @Given /^I create a new "([^"]*)" import$/
     */
    public function iCreateANewImport($importTitle)
    {
        $this->getPage('Import index')->clickImportCreationLink($importTitle);
        $this->wait();
        $this->getNavigationContext()->currentPage = 'Import creation';
    }

    /**
     * @Given /^I try to create an unknown import$/
     */
    public function iTryToCreateAnUnknownImport()
    {
        $this->openPage('Import creation');
    }

    /**
     * @param string $message
     * @param string $property
     *
     * @Then /^I should see "([^"]*)" next to the (\w+)$/
     */
    public function iShouldSeeNextToThe($message, $property)
    {
        if ($message !== $error = $this->getPage('Export show')->getPropertyErrorMessage($property)) {
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
     * @param string $type
     *
     * @When /^I launch the (import|export) job$/
     */
    public function iExecuteTheJob($type)
    {
        $this->getPage(sprintf('%s show', ucfirst($type)))->execute();
        sleep(10);
        $this->getMainContext()->reload();
        $this->wait();
    }

    /**
     * @param string $file
     *
     * @Given /^I upload and import the file "([^"]*)"$/
     */
    public function iUploadAndImportTheFile($file)
    {
        $this
            ->getPage('Import show')
            ->uploadAndImportFile($this->replacePlaceholders($file));
        sleep(10);
        $this->getMainContext()->reload();
        $this->wait();
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
     * @param string    $fileName
     * @param TableNode $table
     *
     * @Given /^the category order in the file "([^"]*)" should be following:$/
     */
    public function theCategoryOrderInTheFileShouldBeFollowing($fileName, TableNode $table)
    {
        if (!file_exists($fileName)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $fileName));
        }

        $categories = array();
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
     */
    public function iCopyTheFileTo($original, $target)
    {
        if (!file_exists($original)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $original));
        }

        copy($original, $target);
    }

    /**
     * @param integer $original
     * @param integer $target
     * @param string  $fileName
     *
     * @Given /^I move the row (\d+) to row (\d+) in the file "([^"]*)"$/
     */
    public function iMoveTheRowToRowInTheFile($original, $target, $fileName)
    {
        if (!file_exists($fileName)) {
            throw $this->createExpectationException(sprintf('File %s does not exist.', $fileName));
        }

        $file = file($fileName);

        $row = $file[$original];
        unset($file[$original]);

        array_splice($file, $target, 0, $row);

        file_put_contents($fileName, $file);
    }

    /**
     * @Then /^I should see the uploaded image$/
     */
    public function iShouldSeeTheUploadedImage()
    {
        $maxTime = 10000;

        while ($maxTime > 0) {
            $this->wait(1000, false);
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
     */
    public function attributeShouldBeRequiredInChannels($attribute, $not, $channels)
    {
        $channels = $this->listToArray($channels);
        $expectation = $not === '';
        foreach ($channels as $channel) {
            if ($expectation !== $this->getPage('Family edit')->isAttributeRequired($attribute, $channel)) {
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
        $this->getPage('Family edit')->switchAttributeRequirement($attribute, $channel);
    }

    /**
     * @Then /^I should see the completeness summary$/
     */
    public function iShouldSeeTheCompletenessSummary()
    {
        $this->getPage('Product edit')->findCompletenessContent();
        $this->getPage('Product edit')->findCompletenessLegend();
    }

    /**
     * @param TableNode $table
     *
     * @Then /^I should see the completeness:$/
     */
    public function iShouldSeeTheCompleteness(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $channel = strtoupper($data['channel']);
            $locale  = $data['locale'];

            try {
                $this->getPage('Product edit')->checkCompletenessState($channel, $locale, $data['state']);
                $this->getPage('Product edit')->checkCompletenessRatio($channel, $locale, $data['ratio']);
                $this->getPage('Product edit')->checkCompletenessMessage($channel, $locale, $data['message']);
            } catch (\InvalidArgumentException $e) {
                throw $this->createExpectationException($e->getMessage());
            }
        }
    }

    /**
     * @param string $email
     *
     * @Given /^an email to "([^"]*)" should have been sent$/
     */
    public function anEmailToShouldHaveBeenSent($email)
    {
        $recorder = $this->getMailRecorder();
        if (0 === $recorder->getMailsSentTo($email)) {
            throw $this->createExpectationException(
                sprintf(
                    'No emails were sent to %s.',
                    $email
                )
            );
        }
    }

    /**
     * @param integer $seconds
     *
     * @Then /^I wait (\d+) seconds$/
     */
    public function iWaitSeconds($seconds)
    {
        $this->wait($seconds * 1000, false);
    }

    /**
     * @param string $products
     *
     * @When /^I mass-edit products (.*)$/
     */
    public function iMassEditProducts($products)
    {
        $page = $this->getPage('Product index');

        foreach ($this->listToArray($products) as $product) {
            $page->selectRow($product);
        }

        $page->massEdit();
        $this->wait();
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
        $this->getCurrentPage()->next();
        $this->scrollContainerTo(900);
        $this->getCurrentPage()->confirm();
        $this->wait();
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
     * @Then /^I click on the Akeneo logo$/
     */
    public function iClickOnTheAkeneoLogo()
    {
        $this->getCurrentPage()->clickOnAkeneoLogo();
    }

    /**
     * @param integer $y
     */
    private function scrollContainerTo($y)
    {
        $this->getSession()->executeScript(sprintf('$(".scrollable-container").scrollTop(%d);', $y));
    }

    /**
     * @param string $page
     * @param array  $options
     *
     * @return Page
     */
    private function openPage($page, array $options = array())
    {
        $page = $this->getNavigationContext()->openPage($page, $options);
        $this->wait();

        return $page;
    }

    /**
     * @return Page
     */
    private function getCurrentPage()
    {
        return $this->getNavigationContext()->getCurrentPage();
    }

    /**
     * @param string $field
     *
     * @return string
     */
    private function getInvalidValueFor($field)
    {
        switch (strtolower($field)) {
            case 'family edit.code':
                return 'inv@lid';
            case 'attribute creation.code':
                return $this->lorem(20);
            case 'attribute creation.description':
                return $this->lorem(256);
            default:
                return '!@#-?_'.$this->lorem(250);
        }
    }

    /**
     * @param integer $length
     *
     * @return string
     */
    private function lorem($length = 100)
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
     * @param integer $time
     * @param string  $condition
     *
     * @return void
     */
    private function wait($time = 5000, $condition = null)
    {
        $this->getMainContext()->wait($time, $condition);
    }

    /**
     * @return FixturesContext
     */
    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    /**
     * @return NavigationContext
     */
    private function getNavigationContext()
    {
        return $this->getMainContext()->getSubcontext('navigation');
    }

    /**
     * @param string $list
     *
     * @return array
     */
    private function listToArray($list)
    {
        return $this->getMainContext()->listToArray($list);
    }

    /**
     * @param string $language
     *
     * @return string
     */
    private function getLocaleCode($language)
    {
        return $this->getFixturesContext()->getLocaleCode($language);
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

    /**
     * Get the mail recorder
     *
     * @return MailRecorder
     */
    private function getMailRecorder()
    {
        return $this->getMainContext()->getMailRecorder();
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function replacePlaceholders($value)
    {
        return $this->getMainContext()->getSubcontext('fixtures')->replacePlaceholders($value);
    }
}
