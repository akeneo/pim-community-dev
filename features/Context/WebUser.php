<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Exception\PendingException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Behat\Context\Step;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;

/**
 * Context of the website
 *
 * @author    Gildas Quéméner <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class WebUser extends RawMinkContext implements PageObjectAwareInterface
{
    private $pageFactory = null;

    private $currentPage = null;

    private $username = null;

    private $password = null;

    private $pageMapping = array(
        'channels'   => 'Channel index',
        'currencies' => 'Currency index',
        'exports'    => 'Export index',
        'families'   => 'Family index',
        'imports'    => 'Import index',
        'locales'    => 'Locale index',
        'products'   => 'Product index',
    );

    /* -------------------- Page-related methods -------------------- */

    /**
     * @BeforeScenario
     */
    public function resetCurrentPage()
    {
        $this->currentPage = null;
    }

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @param string $name
     *
     * @return Page
     */
    public function getPage($name)
    {
        if (null === $this->pageFactory) {
            throw new \RuntimeException('To create pages you need to pass a factory with setPageFactory()');
        }

        $name = implode('\\', array_map('ucfirst', explode(' ', $name)));

        return $this->pageFactory->createPage($name);
    }

    /**
     * @param string $username
     *
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $password = $username.'pass';
        $this->getFixturesContext()->getOrCreateUser($username, $password);

        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @param string $page
     *
     * @Given /^I am on the ([^"]*) page$/
     */
    public function iAmOnThePage($page)
    {
        $page = isset($this->pageMapping[$page]) ? $this->pageMapping[$page] : $page;
        $this->openPage($page);
        $this->wait();
    }

    /**
     * @param string $identifier
     * @param string $page
     *
     * @Given /^I edit the "([^"]*)" (\w+)$/
     * @Given /^I am on the "([^"]*)" (\w+) page$/
     */
    public function iAmOnTheEntityEditPage($identifier, $page)
    {
        $page = ucfirst($page);
        $method = sprintf('get%s', $page);
        $entity = $this->$method($identifier);
        $this->openPage(sprintf('%s edit', $page), array('id' => $entity->getId()));
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
        $this->currentPage = sprintf('%s creation', $entity);
        $this->wait();
    }

    /**
     * @param string $code
     *
     * @Given /^I am on the category "([^"]*)" node creation page$/
     */
    public function iAmOnTheCategoryNodeCreationPage($code)
    {
        $this->openPage('Category node creation', array('id' => $this->getCategory($code)->getId()));
    }

    /**
     * @param string $page
     *
     * @Then /^I should be redirected on the (.*) page$/
     */
    public function iShouldBeRedirectedOnThePage($page)
    {
        $this->assertSession()->addressEquals($this->getPage($page)->getUrl());
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
     * @param string $currencies
     *
     * @Then /^I should see activated currency (.*)$/
     * @Then /^I should see activated currencies (.*)$/
     */
    public function iShouldSeeActivatedCurrencies($currencies)
    {
        foreach ($this->listToArray($currencies) as $currency) {
            if (!$this->getPage('Currency index')->findActivatedCurrency($currency)) {
                throw $this->createExpectationException(sprintf('Currency "%s" is not activated.', $currency));
            }
        }
    }

    /**
     * @param string $currencies
     *
     * @Given /^I should see deactivated currency (.*)$/
     * @Given /^I should see deactivated currencies (.*)$/
     */
    public function iShouldSeeDeactivatedCurrencies($currencies)
    {
        foreach ($this->listToArray($currencies) as $currency) {
            if (!$this->getPage('Currency index')->findDeactivatedCurrency($currency)) {
                throw $this->createExpectationException(sprintf('Currency "%s" is not activated.', $currency));
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
     * @Given /^I should be on the locales page$/
     */
    public function iShouldBeOnTheLocalesPage()
    {
        $this->assertSession()->addressEquals(
            $this->getPage('Locale index')->getUrl()
        );
    }

    /**
     * @Given /^I should be on the locale creation page$/
     */
    public function iShouldBeOnTheLocaleCreationPage()
    {
        $this->openPage('Locale creation');
        $this->wait();
    }

    /**
     * @When /^I should see activated locales? (.*)$/
     */
    public function iShouldSeeActivatedLocales($locales)
    {
        foreach ($this->listToArray($locales) as $locale) {
            if (!$this->getPage('Locale index')->findActivatedLocale($locale)) {
                throw $this->createExpectationException(
                    sprintf('Locale "%s" is not activated', $locale)
                );
            }
        }
    }

    /**
     * @When /^I should see deactivated locales? (.*)$/
     */
    public function iShouldSeeDeactivatedLocales($locales)
    {
        foreach ($this->listToArray($locales) as $locale) {
            if (!$this->getPage('Locale index')->findDeactivatedLocale($locale)) {
                throw $this->createExpectationException(
                    sprintf('Locale "%s" is not deactivated', $locale)
                );
            }
        }
    }

    /**
     * @When /^I should not see locales? (.*)$/
     */
    public function iShouldNotSeeLocales($locales)
    {
        foreach ($this->listToArray($locales) as $locale) {
            try {
                $gridRow = $this->getPage('Locale index')->getGridRow($locale);
                $this->createExpectationException(
                    sprintf('Locale "%s" should not be seen', $locale)
                );
            } catch (\InvalidArgumentException $e) {
                // here we must catch an exception because the row is not found
                continue;
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
        $this->getPage('Product edit')->switchLocale($locale);
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
     * @param string $entity
     * @param string $entityType
     *
     * @Given /^I try to ([^"]*) "([^"]*)" from the ([^"]*) grid$/
     */
    public function iTryToDoActionFromTheGrid($action, $entity, $entityType)
    {
        $entityType = ucfirst(strtolower($entityType));
        $entityPage = $entityType.' index';

        $page = $this->getPage($entityPage);
        if (!$page) {
            throw $this->createExpectationException(sprintf('Unable to find page "%s"', $pageName));
        }

        $getter = 'get'.$entityType;
        if (!method_exists($this, $getter)) {
            throw $this->createExpectationException(sprintf('Cannot find method "%s"', $getter));
        }

        $entity = $this->$getter($entity);

        $action = ucfirst(strtolower($action));

        $page->clickOnAction($entity->getSku(), $action);
    }

    /**
     * @param string $action
     *
     * @Given /^I confirm the ([^"]*)$/
     */
    public function iConfirmThe($action)
    {
        $this->getCurrentPage()->confirmDialog();

        $this->wait();
    }

    /**
     * @param string $action
     *
     * @Given /^I cancel the ([^"]*)$/
     */
    public function iCancelThe($action)
    {
        $this->getCurrentPage()->cancelDialog();
    }

    /**
     * @param string $languages
     *
     * @Then /^I should see that the product is available in (.*)$/
     */
    public function iShouldSeeLanguages($languages)
    {
        $languages = $this->listToArray($languages);
        foreach ($languages as $language) {
            if (null === $this->getPage('Product edit')->findLocaleLink($language)) {
                throw $this->createExpectationException(
                    sprintf(
                        'Expecting to see a locale link for "%s", but didn\'t',
                        $language
                    )
                );
            }
        }

    }

    /**
     * @param string $languages
     *
     * @When /^I add the (.*) languages?$/
     */
    public function iAddTheLanguages($languages)
    {
        $languages = $this->listToArray($languages);
        foreach ($languages as $language) {
            $this->getPage('Product edit')->selectLanguage($language);
        }
    }

    /**
     * @Given /^I save the (.*)$/
     */
    public function iSave()
    {
        $this->getCurrentPage()->save();
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
        $page       = $this->getPage('Product edit');
        $attributes = $this->listToArray($attributes);
        $page->visitGroup($group);

        $group = $this->getGroup($group) ?: 'Other';


        if (count($attributes) !== $actual = $this->getPage('Product edit')->getFieldsCountFor($group)) {
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
                return $field->getText();
            },
            $this->getPage('Product edit')->getFieldsForGroup($group)
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
        if ($title !== $actual = $this->getPage('Product edit')->getTitle()) {
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
     * @param string $pattern
     *
     * @Then /^the title of the product should match "([^"]*)"$/
     */
    public function theTitleOfTheProductShouldMatch($pattern)
    {
        if (1 !== preg_match($pattern, $actual = $this->getPage('Product edit')->getTitle())) {
            throw $this->createExpectationException(
                sprintf(
                    'Expected product title to match "%s", actually saw "%s"',
                    $pattern,
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
        $actual = $this->getPage('Product edit')->findField($fieldName)->getValue();

        if ($expected !== $actual) {
            throw new \LogicException(
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
     * @When /^I change the (?P<field>\w+) to "([^"]*)"$/
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

        $value = $value ?: $this->getInvalidValueFor(sprintf('%s.%s', $this->currentPage, $field));

        return $this->getSession()->getPage()->fillField($field, $value);
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
            throw new ExpectationException(
                sprintf(
                    'Expecting to see attribute %s under group %s, but was not present.',
                    $attribute,
                    $group
                )
            );
        }
    }

    /**
     * @param string $family
     *
     * @Given /^I should be on the "([^"]*)" family page$/
     */
    public function iShouldBeOnTheFamilyPage($family)
    {
        $expectedAddress = $this->getPage('Family edit')->getUrl(array('id' => $this->getFamily($family)->getId()));
        $this->assertSession()->addressEquals($expectedAddress);
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
     * @Given /^the fields (.*) should be disabled$/
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
     * @param TableNode $table
     *
     * @Given /^the following attribute types should have the following fields$/
     */
    public function theFollowingAttributeTypesShouldHaveTheFollowingFields(TableNode $table)
    {
        foreach ($table->getRowsHash() as $type => $fields) {
            $this->iSelectTheAttributeType($type);
            $this->iShouldSeeTheFields($fields);
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
            $this->getCurrentPage()->addOption($data['Default value'], $data['Selected by default']);
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
     * @param string $locale
     *
     * @Given /^I select the (\w+) activated locale$/
     */
    public function iSelectTheActivatedLocale($locale)
    {
        $this->getCurrentPage()->selectActivatedLocale($locale);
    }

    /**
     * @Given /^I disable the product$/
     */
    public function iDisableTheProduct()
    {
        $this->getPage('Product edit')->disableProduct()->save();
    }

    /**
     * @Given /^I enable the product$/
     */
    public function iEnableTheProduct()
    {
        $this->getPage('Product edit')->enableProduct()->save();
    }

    /**
     * @param string $sku
     *
     * @Given /^product "([^"]*)" should be disabled$/
     */
    public function productShouldBeDisabled($sku)
    {
        if ($this->getProduct($sku)->isEnabled()) {
            throw $this->createExpectationException('Product was expected to be be disabled');
        }
    }

    /**
     * @param string $sku
     *
     * @Given /^product "([^"]*)" should be enabled$/
     */
    public function productShouldBeEnabled($sku)
    {
        if (!$this->getProduct($sku)->isEnabled()) {
            throw $this->createExpectationException('Product was expected to be be enabled');
        }
    }

    /**
     * @param string $channels
     *
     * @Then /^I should see channels (.*)$/
     */
    public function iShouldSeeChannels($channels)
    {
        $channels = $this->listToArray($channels);

        foreach ($channels as $channel) {
            if (!$this->getPage('Channel index')->getGridRow($channel)) {
                throw $this->createExpectationException(sprintf('Expecting to see channel %s, not found', $channel));
            }
        }
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
     */
    public function thereShouldBeUpdate($count)
    {
        if ((int) $count !== $countUpdates = $this->getPage('Product edit')->countUpdates()) {
            throw $this->createExpectationException(sprintf('Expected %d updates, saw %d.', $count, $countUpdates));
        }
    }

    /**
     * @param string $code
     *
     * @Given /^I filter per category "([^"]*)"$/
     */
    public function iFilterPerCategory($code)
    {
        $category = $this->getCategory($code);
        $this->getPage('Product index')->clickCategoryFilterLink($category);
        $this->wait();
    }

    /**
     * @Given /^I filter per unclassified category$/
     */
    public function iFilterPerUnclassifiedCategory()
    {
        $this->getPage('Product index')->clickUnclassifiedCategoryFilterLink();
        $this->wait();
    }

    /**
     * @param string $code
     *
     * @Given /^I filter per family ([^"]*)$/
     */
    public function iFilterPerFamily($code)
    {
        $this->getPage('Product index')->filterPerFamily($code);
        $this->wait();
    }

    /**
     * @param string $products
     *
     * @Then /^I should see products (.*)$/
     */
    public function iShouldSeeProducts($products)
    {
        $products = $this->listToArray($products);
        foreach ($products as $product) {
            if (!$this->getPage('Product index')->getGridRow($product)) {
                throw $this->createExpectationException(sprintf('Expecting to see product %s, not found', $product));
            }
        }
    }

    /**
     * @param string $product
     *
     * @Then /^I should see product "([^"]*)"$/
     */
    public function iShouldSeeProduct($product)
    {
        if (!$this->getPage('Product index')->getGridRow($product)) {
            throw $this->createExpectationException(sprintf('Expecting to see product %s, not found', $product));
        }
    }

    /**
     * @param string $products
     *
     * @Then /^I should not see products (.*)$/
     */
    public function iShouldNotSeeProducts($products)
    {
        $products = $this->listToArray($products);
        foreach ($products as $product) {
            try {
                $this->getPage('Product index')->getGridRow($product);
            } catch (\InvalidArgumentException $e) {
                continue;
            }
            throw $this->createExpectationException(sprintf('Expecting not to see product %s, but I see it', $product));
        }
    }

    /**
     * @param string $product
     *
     * @Then /^I should not see product "([^"]*)"$/
     */
    public function iShouldNotSeeProduct($product)
    {
        try {
            $this->getPage('Product index')->getGridRow($product);
        } catch (\InvalidArgumentException $e) {
            return;
        }
        throw $this->createExpectationException(sprintf('Expecting not to see product %s, but I see it', $product));
    }

    /**
     * @param string $code
     *
     * @Then /^I should be on the category "([^"]*)" edit page$/
     */
    public function iShouldBeOnTheCategoryEditPage($code)
    {
        $this->assertSession()->addressEquals($this->getPage('Category edit')->getUrl($this->getCategory($code)));
    }

    /**
     * @param string $exportTitle
     *
     * @Given /^I create a new "([^"]*)" export$/
     */
    public function iCreateANewExport($exportTitle)
    {
        $this->getPage('Export index')->clickExportCreationLink($exportTitle);
        $this->currentPage = 'Export creation';
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
        $this->currentPage = 'Import creation';
    }

    /**
     * @Given /^I try to create an unknown import$/
     */
    public function iTryToCreateAnUnknownImport()
    {
        $this->openPage('Import creation');
    }

    /**
     * @param string $job
     *
     * @Then /^I should be on the "([^"]*)" import job page$/
     */
    public function iShouldBeOnTheImportJobPage($job)
    {
        $expectedAddress = $this->getPage('Import show')->getUrl($this->getJob($job));
        $this->assertSession()->addressEquals($expectedAddress);
    }

    /**
     * @param string $job
     *
     * @Given /^I am on the "([^"]*)" import job page$/
     */
    public function iAmOnTheImportJobPage($job)
    {
        $this->openPage('Import show', array('id' => $this->getJob($job)->getId()));
        $this->wait();
    }

    /**
     * @param string $job
     *
     * @When /^I launch the "([^"]*)" import job$/
     */
    public function iLaunchTheImportJob($job)
    {
        $this->openPage('Import launch', array('id' => $this->getJob($job)->getId()));
    }

    /**
     * @param string $column
     * @param string $exportCode
     * @param string $status
     *
     * @return Step
     * @Then /^the column "([^"]*)" of the row "([^"]*)" should contain the value "([^"]*)"$/
     */
    public function theColumnOfTheRowShouldContainTheValue($column, $exportCode, $status)
    {
        return new Step\Given(
            sprintf(
                'Value of column "%s" of the row which contains "%s" should be "%s"',
                $column,
                $exportCode,
                $status
            )
        );
    }

    /**
     * @param string $job
     *
     * @Then /^I should be on the "([^"]*)" export job page$/
     */
    public function iShouldBeOnTheExportJobPage($job)
    {
        $expectedAddress = $this->getPage('Export show')->getUrl($this->getJob($job));
        $this->assertSession()->addressEquals($expectedAddress);
    }

    /**
     * @param string $job
     *
     * @Given /^I am on the "([^"]*)" export job page$/
     */
    public function iAmOnTheExportJobPage($job)
    {
        $this->openPage('Export show', array('id' => $this->getJob($job)->getId()));
        $this->wait();
    }

    /**
     * @param string $jobCode
     *
     * @return Step
     * @When /^I delete the "([^"]*)" job$/
     */
    public function iDeleteTheJob($jobCode)
    {
        return new Step\Given(
            sprintf(
                'I click on the "Delete" action of the row which contains "%s"',
                $jobCode
            )
        );
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
     * @param string $job
     *
     * @When /^I launch the "([^"]*)" export job$/
     */
    public function iLaunchTheExportJob($job)
    {
        $this->openPage('Export launch', array('id' => $this->getJob($job)->getId()));
    }

    /**
     * @When /^I launch the export job$/
     */
    public function iExecuteTheExportJob()
    {
        $this->getPage('Export show')->execute();
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
     * @param string $page
     * @param array  $options
     *
     * @return Page
     */
    private function openPage($page, array $options = array())
    {
        $this->currentPage = $page;

        $page = $this->getCurrentPage()->open($options);
        $this->loginIfRequired();
        $this->wait();

        return $page;
    }

    /**
     * @return Page
     */
    private function getCurrentPage()
    {
        return $this->getPage($this->currentPage);
    }

    /**
     * A method that logs the user in with the previously provided credentials if required by the page
     */
    private function loginIfRequired()
    {
        $loginForm = $this->getCurrentPage()->find('css', '.form-signin');
        if ($loginForm) {
            $loginForm->fillField('_username', $this->username);
            $loginForm->fillField('_password', $this->password);
            $loginForm->pressButton('Log in');
        }
    }

    /**
     * @param string $field
     *
     * @return string
     */
    private function getInvalidValueFor($field)
    {
        switch ($field) {
            case 'Family edit.Code':
                return 'inv@lid';
        }
    }

    /**
     * @param integer $time
     * @param string  $condition
     *
     * @return void
     */
    private function wait($time = 5000, $condition = 'document.readyState == "complete" && !$.active')
    {
        try {
            return $this->getMainContext()->wait($time, $condition);
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @param string $sku
     *
     * @return Product
     */
    private function getProduct($sku)
    {
        return $this->getFixturesContext()->getProduct($sku);
    }

    /**
     * @param string $code
     *
     * @return Category
     */
    private function getCategory($code)
    {
        return $this->getFixturesContext()->getCategory($code);
    }

    /**
     * @param string $name
     *
     * @return AttributeGroup
     */
    private function getGroup($name)
    {
        return $this->getFixturesContext()->getGroup($name);
    }

    /**
     * @param string $type
     *
     * @return ProductAttribute
     */
    private function getAttribute($type)
    {
        return $this->getFixturesContext()->getAttribute($type);
    }

    /**
     * @param string $code
     *
     * @return Family
     */
    private function getFamily($code)
    {
        return $this->getFixturesContext()->getFamily($code);
    }

    /**
     * @return FixturesContext
     */
    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
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
     * @param string $job
     *
     * @return Job
     */
    private function getJob($job)
    {
        return $this->getFixturesContext()->getJob($job);
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
