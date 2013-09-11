<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Exception\ExpectationException;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Exception\PendingException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Behat\Context\Step;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;

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
        'attributes'  => 'Attribute index',
        'channels'    => 'Channel index',
        'currencies'  => 'Currency index',
        'exports'     => 'Export index',
        'families'    => 'Family index',
        'imports'     => 'Import index',
        'locales'     => 'Locale index',
        'products'    => 'Product index',
        'users'       => 'User index',
        'user roles'  => 'UserRole index',
        'user groups' => 'UserGroup index',
        'categories'  => 'Category tree creation',
        'home'        => 'Base index',
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
     * @BeforeScenario
     */
    public function clearRecordedMails()
    {
        $this->getMailRecorder()->clear();
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
        $password = $username;
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

            assertEquals($url, $currentUrl, sprintf('Error ocurred on page "%s"', $data['page']));

            $loadedCorrectly = (bool) $this->getSession()->evaluateScript('return $(\'img[alt="Akeneo"]\').length;');
            assertTrue($loadedCorrectly, sprintf('Javascript error ocurred on page "%s"', $data['page']));
        }
    }

    /**
     * @param string $category
     *
     * @Given /^I expand the "([^"]*)" category$/
     */
    public function iExpandTheCategory($category)
    {
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
     * @param string $page
     *
     * @Then /^I should be redirected on the (.*) page$/
     */
    public function iShouldBeRedirectedOnThePage($page)
    {
        $this->assertAddress($this->getPage($page)->getUrl());
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
     * @Given /^I should be on the locales page$/
     */
    public function iShouldBeOnTheLocalesPage()
    {
        $this->assertAddress($this->getPage('Locale index')->getUrl());
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
            throw $this->createExpectationException(sprintf('Unable to find page "%s"', $entityPage));
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
        $page       = $this->getPage('Product edit');
        $attributes = $this->listToArray($attributes);
        $page->visitGroup($group);

        $group = $this->getGroup($group) ?: AttributeGroup::DEFAULT_GROUP_CODE;

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

        $value = $value ?: $this->getInvalidValueFor(sprintf('%s.%s', $this->currentPage, $field));

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
     * @param string $group
     *
     * @Given /^I should be on the "([^"]*)" group page$/
     */
    public function iShouldBeOnTheGroupPage($group)
    {
        $expectedAddress = $this->getPage('Group edit')->getUrl(array('id' => $this->getGroup($group)->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param string $family
     *
     * @Given /^I should be on the "([^"]*)" family page$/
     */
    public function iShouldBeOnTheFamilyPage($family)
    {
        $expectedAddress = $this->getPage('Family edit')->getUrl(array('id' => $this->getFamily($family)->getId()));
        $this->assertAddress($expectedAddress);
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
        $this->wait(10000, null);
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
        $this->wait(10000, null);
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
     * @Given /^I disable the products$/
     */
    public function iDisableTheProducts()
    {
        $this->getPage('Batch ChangeStatus')->disableProducts()->next();
    }

    /**
     * @Given /^I enable the product$/
     */
    public function iEnableTheProduct()
    {
        $this->getPage('Product edit')->enableProduct()->save();
    }

    /**
     * @Given /^I enable the products$/
     */
    public function iEnableTheProducts()
    {
        $this->getPage('Batch ChangeStatus')->enableProducts()->next();
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
     * @param string $value
     * @param string $currency
     *
     * @When /^I filter per price with value "([^"]*)" and currency "([^"]*)"$/
     */
    public function iFilterPerPrice($value, $currency)
    {
        $this->getPage('Product index')->filterPerPrice($value, $currency);
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
     * @param string $code
     *
     * @Given /^I filter per channel ([^"]*)$/
     */
    public function iFilterPerChannel($code)
    {
        $this->getPage('Product index')->filterPerChannel($code);
        $this->wait();
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
     * @param string $code
     *
     * @Then /^I should be on the category "([^"]*)" edit page$/
     */
    public function iShouldBeOnTheCategoryEditPage($code)
    {
        $expectedAddress = $this->getPage('Category edit')->getUrl(array('id' => $this->getCategory($code)->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param string $code
     *
     * @Given /^I should be on the category "([^"]*)" node creation page$/
     */
    public function iShouldBeOnTheCategoryNodeCreationPage($code)
    {
        $id = $this->getCategory($code)->getId();
        $expectedAddress = $this->getPage('Category node creation')->getUrl(array('id' => $id));
        $this->assertAddress($expectedAddress);
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

        //TODO Otherwise, it  makes the features/category/create_a_category.feature:28 scenario fails
        $this->wait(5000, null);
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
        $this->wait();
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
     * @param string $code
     *
     * @Then /^I should be on the "([^"]*)" import job page$/
     */
    public function iShouldBeOnTheImportJobPage($code)
    {
        $expectedAddress = $this->getPage('Import show')->getUrl(array('id' => $this->getJobInstance($code)->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param string $code
     *
     * @Given /^I am on the "([^"]*)" import job page$/
     */
    public function iAmOnTheImportJobPage($code)
    {
        $this->openPage('Import show', array('id' => $this->getJobInstance($code)->getId()));
        $this->wait();
    }

    /**
     * @param string $code
     *
     * @When /^I launch the "([^"]*)" import job$/
     */
    public function iLaunchTheImportJob($code)
    {
        $this->openPage('Import launch', array('id' => $this->getJobInstance($code)->getId()));
    }

    /**
     * @param string $column
     * @param string $exportCode
     * @param string $status
     *
     * @return Step\Given
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
     * @param string $code
     *
     * @Then /^I should be on the "([^"]*)" export job page$/
     */
    public function iShouldBeOnTheExportJobPage($code)
    {
        $expectedAddress = $this->getPage('Export show')->getUrl(array('id' => $this->getJobInstance($code)->getId()));
        $this->assertAddress($expectedAddress);
    }

    /**
     * @param string $code
     *
     * @Given /^I am on the "([^"]*)" export job page$/
     */
    public function iAmOnTheExportJobPage($code)
    {
        $this->openPage('Export show', array('id' => $this->getJobInstance($code)->getId()));
        $this->wait();
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
     * @param string $code
     *
     * @When /^I launch the "([^"]*)" export job$/
     */
    public function iLaunchTheExportJob($code)
    {
        $this->openPage('Export launch', array('id' => $this->getJobInstance($code)->getId()));
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
     * @Then /^I should see the uploaded image$/
     */
    public function iShouldSeeTheUploadedImage()
    {
        $this->wait(3000, '');
        if (!$this->getPage('Product edit')->getImagePreview()) {
            throw $this->createExpectationException('Image preview is not displayed.');
        }
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
     * @param string $state
     * @param string $channel
     * @param string $locale
     *
     * @Given /^I should see the completeness state "([^"]*)" for channel "([^"]*)" and locale "([^"]*)"$/
     */
    public function iShouldSeeCompletenessStateForChannelAndLocale($state, $channel, $locale)
    {
        $channelCode = strtoupper($channel);

        try {
            $this->getPage('Product edit')->checkCompletenessState($channelCode, $locale, $state);
        } catch (\InvalidArgumentException $e) {
            throw $this->createExpectationException($e->getMessage());
        }
    }

    /**
     * @param string $ratio
     * @param string $channel
     * @param string $locale
     *
     * @Given /^I should see the completeness ratio (\d+)% for channel "([^"]*)" and locale "([^"]*)"$/
     */
    public function iShouldSeeTheCompletenessRatioForChannelAndLocale($ratio, $channel, $locale)
    {
        $channelCode = strtoupper($channel);

        try {
            $this->getPage('Product edit')->checkCompletenessRatio($channelCode, $locale, $ratio);
        } catch (\InvalidArgumentException $e) {
            throw $this->createExpectationException($e->getMessage());
        }
    }

    /**
     * @param string $message
     * @param string $channel
     * @param string $locale
     *
     * @Given /^I should see the completeness message "([^"]*)" for channel "([^"]*)" and locale "([^"]*)"$/
     */
    public function iShouldSeeTheCompletenesssMessageForChannelAndLocale($message, $channel, $locale)
    {
        $channelCode = strtoupper($channel);

        try {
            $this->getPage('Product edit')->checkCompletenessMessage($channelCode, $locale, $message);
        } catch (\InvalidArgumentException $e) {
            throw $this->createExpectationException($e->getMessage());
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
     * @When /^I mass-edit products (.*)$/
     */
    public function iMassEditProducts($products)
    {
        $that = $this;
        $products = preg_replace(
            '/]\d+=/',
            ']=',
            http_build_query(
                array_map(
                    function ($product) use ($that) {
                        return $that->getProduct($product)->getId();
                    },
                    $this->listToArray($products)
                ),
                'products[]'
            )
        );

        $this->openPage('Batch Operation', array('products' => $products));
    }

    /**
     * @Given /^I choose the "([^"]*)" operation$/
     */
    public function iChooseTheOperation($operation)
    {
        $this->currentPage = $this
            ->getPage('Batch Operation')
            ->chooseOperation($operation)
            ->next();

        $this->wait();
    }

    /**
     * @param string $expected
     */
    private function assertAddress($expected)
    {
        $actual = $this->getSession()->getCurrentUrl();
        $result = strpos($actual, $expected) !== false;
        assertTrue($result, sprintf('Expecting to be on page "%s", not "%s"', $expected, $actual));
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
    private function wait($time = 5000, $condition = 'document.readyState == "complete" && !$.active')
    {
        try {
            return $this->getMainContext()->wait($time, $condition);
        } catch (UnsupportedDriverActionException $e) {
        }
    }

    /**
     * @param string $username
     *
     * @return User
     */
    private function getUser($username)
    {
        return $this->getFixturesContext()->getUser($username);
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
     * @param string $code
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Channel
     */
    private function getChannel($code)
    {
        return $this->getFixturesContext()->getChannel($code);
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
     * @param string $code
     *
     * @return Job
     */
    private function getJobInstance($code)
    {
        return $this->getFixturesContext()->getJobInstance($code);
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
}
