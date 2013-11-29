<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Behat\Gherkin\Node\TableNode;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Context\Step;
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Model\Product;
use Behat\Mink\Element\Element;

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

        $parentNode = $this->getCurrentPage()->findCategoryInTree($parent);
        $childNode = $parentNode->getParent()->find('css', sprintf('li a:contains(%s)', $child));

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
        $this->getCurrentPage()->visitTab($tab);
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

        $group = $this->getFixturesContext()->findAttributeGroup($group) ?: AttributeGroup::DEFAULT_GROUP_CODE;

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
        $field = $this->getCurrentPage()->findField($fieldName);
        $class = $field->getAttribute('class');
        if (strpos($class, 'select2-focusser') !== false) {
            $field  = $field->getParent()->getParent()->find('css', 'select');
            $actual = $field->find('css', 'option[selected]')->getHtml();
        } elseif (strpos($class, 'select2-input') !== false) {
            $field   = $field->getParent()->getParent()->getParent()->getParent()->find('css', 'select');
            $options = $field->findAll('css', 'option[selected]');
            $actual  = array();
            foreach ($options as $option) {
                $actual[] = $option->getHtml();
            }
            $expected = $this->listToArray($expected);
            sort($actual);
            sort($expected);
            $actual   = implode(', ', $actual);
            $expected = implode(', ', $expected);
        } else {
            $actual = $field->getValue();
        }

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

        $this->getCurrentPage()->fillField($field, $value);
        $this->wait();
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
     * @param string $attributes
     * @param string $group
     *
     * @Given /^I should see attributes? "([^"]*)" in group "([^"]*)"$/
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
     * @param string $field
     *
     * @When /^I add a new option to the "([^"]*)" attribute$/
     */
    public function iAddANewOptionToTheAttribute($field)
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
     * @param string    $popin
     * @param TableNode $table
     *
     * @Given /^I fill in the following information(| in the popin):$/
     */
    public function iFillInTheFollowingInformation($popin, TableNode $table)
    {
        $element = $popin ? $this->getCurrentPage()->find('css', '.ui-dialog') : null;
        foreach ($table->getRowsHash() as $field => $value) {
            $this->getCurrentPage()->fillField($field, $value, $element);
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
        $this->getMainContext()->executeScript('$("[disabled]").removeAttr("disabled");');
    }

    /**
     * @param string $field
     *
     * @Given /^I remove the "([^"]*)" file$/
     */
    public function iRemoveTheFile($field)
    {
        $script = sprintf("$('label:contains(\"%s\")').parent().find('.remove-upload').click();", $field);
        if (!$this->getMainContext()->executeScript($script)) {
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
            $this->iChangeTheTo('Attribute type', $type);
            try {
                $this->getMainContext()->getSubcontext('assertions')->iShouldSeeTheFields($fields);
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
     * @param string $button
     *
     * @Given /^I press the "([^"]*)" button in the popin$/
     */
    public function iPressTheButtonInThePopin($button)
    {
        $this
            ->getCurrentPage()
            ->find('css', sprintf('.ui-dialog button:contains("%s")', $button))
            ->press();
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
     * @param integer $count
     *
     * @Then /^there should be (\d+) updates?$/
     */
    public function thereShouldBeUpdate($count)
    {
        if ((int) $count !== $actualCount = count($this->getCurrentPage()->getHistoryRows())) {
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
     * @Given /^I blur the category node$/
     */
    public function iBlurTheCategoryNode()
    {
        $elt = $this->getCurrentPage()->findInputNodeInTree();
        $elt->blur();
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
     * @param string $type
     *
     * @When /^I launch the (import|export) job$/
     */
    public function iExecuteTheJob($type)
    {
        $this->getPage(sprintf('%s show', ucfirst($type)))->execute();
        $this->wait();
    }

    /**
     * @When /^I wait for the job to finish$/
     */
    public function iWaitForTheJobToFinish()
    {
        $timeout = 120;

        while ($timeout && $refreshLink = $this->getCurrentPage()->findLink('Refresh')) {
            sleep(3);
            $timeout -= 3;
            $refreshLink->click();
            $this->wait();
        }

        if ($refreshLink) {
            throw $this->createExpectationException("The job didn't finish in 2 minutes");
        }
    }

    /**
     * @param string $file
     *
     * @Given /^I upload and import the file "([^"]*)"$/
     */
    public function iUploadAndImportTheFile($file)
    {
        $this->getCurrentPage()->clickLink('Upload and import');
        $this->attachFileToField($this->replacePlaceholders($file), 'Drop a file or click here');
        $this->getCurrentPage()->pressButton('Upload and import now');

        sleep(10);
        $this->getMainContext()->reload();
        $this->wait();
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
        $this->getCurrentPage()->findCompletenessLegend();
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
                $this->getCurrentPage()->checkCompletenessState($channel, $locale, $data['state']);
                $this->getCurrentPage()->checkCompletenessRatio($channel, $locale, $data['ratio']);
                $this->getCurrentPage()->checkCompletenessMessage($channel, $locale, $data['message']);
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
     * @param string $products
     *
     * @When /^I mass-delete products (.*)$/
     */
    public function iMassDeleteProducts($products)
    {
        $page = $this->getPage('Product index');

        foreach ($this->listToArray($products) as $product) {
            $page->selectRow($product);
        }

        $page->massDelete();
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
     * @Then /^I click on the Akeneo logo$/
     */
    public function iClickOnTheAkeneoLogo()
    {
        $this->getCurrentPage()->clickOnAkeneoLogo();
    }

    /**
     * @param string       $code
     * @param PyStringNode $csv
     *
     * @return null
     * @Then /^exported file of "([^"]*)" should contain:$/
     */
    public function exportedFileOfShouldContain($code, PyStringNode $csv)
    {
        $config = $this
            ->getFixturesContext()
            ->getJobInstance($code)->getRawConfiguration();
        $config = reset($config);

        $path = $config['writer']['filePath'];

        if (!is_file($path)) {
            throw $this->createExpectationException(
                sprintf('File "%s" doesn\'t exist', $path)
            );
        }

        $delimiter = isset($config['processor']['delimiter']) ? $config['processor']['delimiter'] : ';';
        $enclosure = isset($config['processor']['enclosure']) ? $config['processor']['enclosure'] : '"';
        $escape    = isset($config['processor']['escape'])    ? $config['processor']['escape']    : '\\';

        $csvFile = new \SplFileObject($path);
        $csvFile->setFlags(
            \SplFileObject::READ_CSV   |
            \SplFileObject::READ_AHEAD |
            \SplFileObject::SKIP_EMPTY |
            \SplFileObject::DROP_NEW_LINE
        );
        $csvFile->setCsvControl($delimiter, $enclosure, $escape);

        $expectedLines = array();
        foreach ($csv->getLines() as $line) {
            if (!empty($line)) {
                $expectedLines[] = explode($delimiter, str_replace($enclosure, '', $line));
            }
        }

        $actualLines = array();
        while ($data = $csvFile->fgetcsv()) {
            if (!empty($data)) {
                $actualLines[] = array_map(
                    function ($item) use ($enclosure) {
                        return str_replace($enclosure, '', $item);
                    },
                    $data
                );
            }
        }

        $expectedCount = count($expectedLines);
        $actualCount   = count($actualLines);
        assertSame(
            $expectedCount,
            $actualCount,
            sprintf('Expecting to see %d rows, found %d', $expectedCount, $actualCount)
        );

        foreach ($expectedLines as $index => $expectedLine) {
            $actualLine = $actualLines[$index];
            sort($expectedLine);
            sort($actualLine);
            assertEquals(
                $expectedLine,
                $actualLine,
                sprintf(
                    'Expecting row %d to be "%s", found "%s"',
                    $index,
                    implode($delimiter, $expectedLine),
                    implode($delimiter, $actualLine)
                )
            );
        }
    }

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Then /^export directory of "([^"]*)" should contain the following media:$/
     */
    public function exportDirectoryOfShouldContainTheFollowingMedia($code, TableNode $table)
    {
        $config = $this
            ->getFixturesContext()
            ->getJobInstance($code)->getRawConfiguration();
        $config = reset($config);

        $path = dirname($config['writer']['filePath']);

        if (!is_dir($path)) {
            throw $this->createExpectationException(
                sprintf('Directory "%s" doesn\'t exist', $path)
            );
        }

        foreach ($table->getRows() as $data) {
            $file = rtrim($path, '/') . '/' .$data[0];

            if (!is_file($file)) {
                throw $this->createExpectationException(
                    sprintf('File \"%s\" doesn\'t exist', $file)
                );
            }
        }
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
    private function wait($time = 10000, $condition = null)
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
