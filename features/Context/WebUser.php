<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Exception\PendingException;

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

    /**
     * @BeforeScenario
     */
    public function resetCurrentPage()
    {
        $this->currentPage = null;
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

        return $this->pageFactory->createPage($name);
    }

    /**
     * @param PageFactory $pageFactory
     */
    public function setPageFactory(PageFactory $pageFactory)
    {
        $this->pageFactory = $pageFactory;
    }

    /**
     * @Then /^I should see activated currency (.*)$/
     * @Then /^I should see activated currencies (.*)$/
     */
    public function iShouldSeeActivatedCurrencies($currencies)
    {
        foreach ($this->listToArray($currencies) as $currency) {
            if (!$this->getPage('Currency index')->findActivatedCurrency($currency)) {
                throw $this->createExpectationException(sprintf(
                    'Currency "%s" is not activated.', $currency
                ));
            }
        }
    }

    /**
     * @Given /^I should see deactivated currency (.*)$/
     * @Given /^I should see deactivated currencies (.*)$/
     */
    public function iShouldSeeDeactivatedCurrencies($currencies)
    {
        foreach ($this->listToArray($currencies) as $currency) {
            if (!$this->getPage('Currency index')->findDeactivatedCurrency($currency)) {
                throw $this->createExpectationException(sprintf(
                    'Currency "%s" is not activated.', $currency
                ));
            }
        }
    }

    /**
     * @When /^I activate the (.*) currency$/
     */
    public function iActivateTheCurrency($currencies)
    {
        $this->getPage('Currency index')->activateCurrencies(
            $this->listToArray($currencies)
        );
        $this->wait(5000, '$("table.grid tbody tr").length > 0');
    }

    /**
     * @When /^I deactivate the (.*) currency$/
     */
    public function iDeactivateTheCurrency($currencies)
    {
        $this->getPage('Currency index')->deactivateCurrencies(
            $this->listToArray($currencies)
        );
        $this->wait(5000, '$("table.grid tbody tr").length > 0');
    }

    /**
     * @When /^I switch the locale to "([^"]*)"$/
     */
    public function iSwitchTheLocaleTo($locale)
    {
        $this->getPage('Product edit')->switchLocale($locale);
    }

    /**
     * @Then /^the locale switcher should contain the following items:$/
     */
    public function theLocaleSwitcherShouldContainTheFollowingItems(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            if (!$this->getPage('Product edit')->findLocaleLink($data['language'], $data['label'])) {
                throw $this->createExpectationException(sprintf(
                    'Could not find locale "%s %s" in the locale switcher', $data['locale'], $data['label']
                ));
            }
        }
    }

    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $password = $username.'pass';
        $this->getFixturesContext()->getOrCreateUser($username, $password);

        $this
            ->openPage('Login')
            ->login($username, $password)
        ;
    }

    /**
     * @Given /^I am on the "([^"]*)" product page$/
     */
    public function iAmOnTheProductPage($product)
    {
        $product = $this->getProduct($product);
        $this->openPage('Product edit', array(
            'id' => $product->getId(),
        ));
    }

    /**
     * @Given /^I create a new product$/
     */
    public function iCreateANewProduct()
    {
        $this->getPage('Product index')->clickNewProductLink();
        $this->wait(5000, '$(".ui-dialog:contains(\"Create a new product\")").css("display") === "block"');
    }

    /**
     * @When /^I am on the "([^"]*)" attribute page$/
     */
    public function iAmOnTheAttributePage($label)
    {
        $attribute = $this->getAttribute($label);

        $this->openPage('Attribute', array(
            'id' => $attribute->getId(),
        ));
    }

    /**
     * @Given /^I am on the currencies page$/
     */
    public function iAmOnTheCurrenciesPage()
    {
        $this->openPage('Currency index');
        $this->wait(5000, '$("table.grid tbody tr").length > 0');
    }

    /**
     * @Given /^I visit the "([^"]*)" tab$/
     */
    public function iVisitTheTab($tab)
    {
        $this->getCurrentPage()->visitTab($tab);
    }

    /**
     * @Then /^I should see that the product is available in (.*)$/
     */
    public function iShouldSeeLanguages($languages)
    {
        $languages = $this->listToArray($languages);
        foreach ($languages as $language) {
            if (null === $this->getPage('Product edit')->findLocaleLink($language)) {
                throw $this->createExpectationException(sprintf('
                    Expecting to see a locale link for "%s", but didn\'t', $language
                ));
            }
        }

    }

    /**
     * @When /^I add the (.*) languages?$/
     */
    public function iAddTheLanguages($languages)
    {
        $languages = $this->listToArray($languages);
        foreach ($languages as $language) {
            $this
                ->getPage('Product edit')
                ->selectLanguage($language)
            ;
        }
    }

    /**
     * @Given /^I save the product$/
     */
    public function iSaveTheProduct()
    {
        $this->getPage('Product edit')->save();
    }

    /**
     * @Given /^I save the family$/
     */
    public function iSaveTheFamily()
    {
        $this->getPage('Family edit')->save();
    }

    /**
     * @Given /^I change the attribute position to (\d+)$/
     */
    public function iChangeTheAttributePositionTo($position)
    {
        $this
            ->getPage('Attribute')
            ->setPosition($position)
            ->save()
        ;
    }

    /**
     * @Then /^I should see "([^"]*)"$/
     */
    public function iShouldSee($text)
    {
        $this->assertSession()->pageTextContains($text);
    }

    /**
     * @Given /^attributes? in group "([^"]*)" should be (.*)$/
     */
    public function attributesInGroupShouldBe($group, $attributes)
    {
        $page       = $this->getPage('Product edit');
        $attributes = $this->listToArray($attributes);
        $page->visitGroup($group);

        $group = $this->getGroup($group) ?: 'Other';


        if (count($attributes) !== $actual = $this->getPage('Product edit')->getFieldsCountFor($group)) {
            throw $this->createExpectationException(sprintf(
                'Expected to see %d fields in group "%s", actually saw %d',
                count($attributes), $group, $actual
            ));
        }

        $labels = array_map(function($field) {
            return $field->getText();
        }, $this->getPage('Product edit')->getFieldsForGroup($group));

        if (count(array_diff($attributes, $labels))) {
            throw $this->createExpectationException(sprintf('
                Expecting to see attributes "%s" in group "%s", but saw "%s".',
                join('", "', $attributes), $group, join('", "', $labels)
            ));
        }
    }

    /**
     * @Then /^the title of the product should be "([^"]*)"$/
     */
    public function theTitleOfTheProductShouldBe($title)
    {
        if ($title !== $actual = $this->getPage('Product edit')->getTitle()) {
            throw $this->createExpectationException(sprintf(
                'Expected product title "%s", actually saw "%s"',
                $title, $actual
            ));
        }
    }

    /**
     * @Then /^the title of the product should match "([^"]*)"$/
     */
    public function theTitleOfTheProductShouldMatch($pattern)
    {
        if (1 !== preg_match($pattern, $actual = $this->getPage('Product edit')->getTitle())) {
            throw $this->createExpectationException(sprintf(
                'Expected product title to match "%s", actually saw "%s"',
                $pattern, $actual
            ));
        }
    }

    /**
     * @Then /^the product (.*) should be empty$/
     * @Then /^the product (.*) should be "([^"]*)"$/
     */
    public function theProductFieldValueShouldBe($fieldName, $expected = '')
    {
        $actual = $this->getPage('Product edit')->findField($fieldName)->getValue();

        if ($expected !== $actual) {
            throw new \LogicException(sprintf(
                'Expected product %s to be "%s", but got "%s".',
                $fieldName, $expected, $actual
            ));
        }
    }

    /**
     * @When /^I change the (?<field>\w+) to "([^"]*)"$/
     * @When /^I change the (?P<language>\w+) (?P<field>\w+) to "(?P<value>[^"]*)"$/
     * @When /^I change the (?P<field>\w+) to an invalid value$/
     */
    public function iChangeTheTo($field, $value = null, $language = null)
    {
        if ($language) {
            try {
                $field = $this->getCurrentPage()->getFieldLocator(
                    $field, $this->getLocaleCode($language)
                );
            } catch (\BadMethodCallException $e) {
                // Use default $field if current page does not provide a getFieldLocator method
            }
        }

        return $this->getSession()->getPage()->fillField(
            $field, $value ?: $this->getInvalidValueFor(sprintf('%s.%s', $this->currentPage, $field))
        );
    }

    /**
     * @Then /^I should (not )?see available attributes? (.*) in group "([^"]*)"$/
     */
    public function iShouldSeeAvailableAttributesInGroup($not, $attributes, $group)
    {
        foreach ($this->listToArray($attributes) as $attribute) {
            $element = $this->getCurrentPage()->getAvailableAttribute($attribute, $group);
            if (!$not) {
                if (!$element) {
                    throw $this->createExpectationException(sprintf(
                        'Expecting to see attribute %s under group %s, but was not present.',
                        $attribute, $group
                    ));
                }
            } else {
                if ($element) {
                    throw $this->createExpectationException(sprintf(
                        'Expecting not to see attribute %s under group %s, but was present.',
                        $attribute, $group
                    ));
                }
            }
        }
    }

    /**
     * @Given /^I add available attributes? (.*)$/
     */
    public function iAddAvailableAttributes($attributes)
    {
        $this->getCurrentPage()->openAvailableAttributesMenu();
        foreach ($this->listToArray($attributes) as $attribute) {
            $this->getCurrentPage()->selectAvailableAttribute($attribute);
        }

        $this->getCurrentPage()->addSelectedAvailableAttributes();
        $this->wait(2000);
    }

    /**
     * @When /^I am on the products page$/
     */
    public function iAmOnTheProductsPage()
    {
        $this->openPage('Product index');
    }

    /**
     * @When /^I am on the families page$/
     */
    public function iAmOnTheFamiliesPage()
    {
        $this->openPage('Family index');
    }

    /**
     * @When /^I am on the family creation page$/
     */
    public function iAmOnTheFamilyCreationPage()
    {
        $this->openPage('Family creation');
    }

    /**
     * @Then /^I should be on the family creation page$/
     */
    public function iShouldBeOnTheFamilyCreationPage()
    {
        $this->assertSession()->addressEquals(sprintf(
            '%s%s', rtrim($this->getMinkParameter('base_url'), '/'), $this->getPage('Family creation')->getUri()
        ));
    }

    /**
     * @Given /^I am on the "([^"]*)" family page$/
     */
    public function iAmOnTheFamilyPage($family)
    {
        $this->openPage('Family edit', array(
            'family_id' => $this->getFamily($family)->getId()
        ));
    }

    /**
     * @Then /^I should see the families (.*)$/
     */
    public function iShouldSeeTheFamilies($families)
    {
        $expectedFamilies = $this->listToArray($families);

        if ($expectedFamilies !== $families = $this->getPage('Family index')->getFamilies()) {
            throw $this->createExpectationException(sprintf(
                'Expecting to see families %s, but saw %s',
                print_r($expectedFamilies, true),
                print_r($families, true)
            ));
        }
    }

    /**
     * @Given /^I edit the "([^"]*)" family$/
     */
    public function iEditTheFamily($family)
    {
        $this->currentPage = 'Family edit';
        $link = $this->getPage('Family index')->getFamilyLink($family);

        if (!$link) {
            throw $this->createExpectationException(sprintf(
                'Couldn\'t find a "%s" link', $family
            ));
        }

        $link->click();
    }

    /**
     * @Given /^I should see attribute "([^"]*)" in group "([^"]*)"$/
     */
    public function iShouldSeeAttributeInGroup($attribute, $group)
    {
        if (!$this->getCurrentPage()->getAttribute($attribute, $group)) {
            throw new ExpectationException(sprintf(
                'Expecting to see attribute %s under group %s, but was not present.',
                $attribute, $group
            ));
        }
    }

    /**
     * @Given /^I should be on the "([^"]*)" family page$/
     */
    public function iShouldBeOnTheFamilyPage($family)
    {
        $expectedAddress = $this->getPage('Family edit')->getUrl(array(
            'family_id' => $this->getFamily($family)->getId(),
        ));
        $this->assertSession()->addressEquals($expectedAddress);
    }

    /**
     * @Then /^I should (not )?see a remove link next to the "([^"]*)" field$/
     */
    public function iShouldSeeARemoveLinkNextToTheField($not, $field)
    {
        $removeLink = $this->getPage('Product edit')->getRemoveLinkFor($field);
        if (!$not) {
            if (!$removeLink) {
                throw $this->createExpectationException(sprintf(
                    'Remove link on field "%s" should not be displayed.', $field
                ));
            }
        } else {
            if ($removeLink) {
                throw $this->createExpectationException(sprintf(
                    'Remove link on field "%s" should be displayed.', $field
                ));
            }
        }
    }

    /**
     * @When /^I remove the "([^"]*)" attribute$/
     */
    public function iRemoveTheAttribute($field)
    {
        if (null === $link = $this->getCurrentPage()->getRemoveLinkFor($field)) {
            throw $this->createExpectationException(sprintf(
                'Remove link on field "%s" should be displayed.', $field
            ));
        }

        $link->click();
        $this->getSession()->getPage()->clickLink('OK');
    }

    /**
     * @Then /^eligible attributes as label should be (.*)$/
     */
    public function eligibleAttributesAsLabelShouldBe($attributes)
    {
        $expectedAttributes = $this->listToArray($attributes);
        $options = $this->getPage('Family edit')->getAttributeAsLabelOptions();

        if (count($expectedAttributes) !== $actual = count($options)) {
            throw $this->createExpectationException(sprintf(
                'Expected to see %d eligible attributes as label, actually saw %d:'."\n%s",
                count($expectedAttributes), $actual, print_r($options, true)
            ));
        }

        if ($expectedAttributes !== $options) {
            throw $this->createExpectationException(sprintf(
                'Expected to see eligible attributes as label %s, actually saw %s',
                print_r($expectedAttributes, true), print_r($options, true)
            ));
        }
    }

    /**
     * @Given /^I choose "([^"]*)" as the label of the family$/
     */
    public function iChooseAsTheLabelOfTheFamily($attribute)
    {
        $this
            ->getPage('Family edit')
            ->selectAttributeAsLabel($attribute)
            ->save()
        ;
    }

    /**
     * @When /^I select the attribute type "([^"]*)"$/
     */
    public function iSelectTheAttributeType($type)
    {
        $this
            ->getPage('Attribute creation')
            ->selectAttributeType($type)
        ;

        $this->wait(2000);
    }

    /**
     * @Given /^I am on the attribute creation page$/
     */
    public function iAmOnTheAttributeCreationPage()
    {
        $this->openPage('Attribute creation');
    }

    /**
     * @Given /^I am on the channels page$/
     */
    public function iAmOnTheChannelsPage()
    {
        $this->openPage('Channel index');
        $this->wait(5000, '$("table.grid tbody tr").length > 0');
    }

    /**
     * @Given /^I am on the channel creation page$/
     */
    public function iAmOnTheChannelCreationPage()
    {
        $this->openPage('Channel creation');
    }

    /**
     * @Then /^I should see the (.*) fields?$/
     */
    public function iShouldSeeTheFields($fields)
    {
        $fields = $this->listToArray($fields);
        foreach ($fields as $field) {
            if (!$this->getCurrentPage()->findField($field)) {
                throw $this->createExpectationException(sprintf(
                    'Expecting to see field "%s".', $field
                ));
            }
        }
    }

    /**
     * @Given /^the fields (.*) should be disabled$/
     */
    public function theFieldsShouldBeDisabled($fields)
    {
        $fields = $this->listToArray($fields);
        foreach ($fields as $fieldName) {
            $field = $this->getCurrentPage()->findField($fieldName);
            if (!$field) {
                throw $this->createExpectationException(sprintf(
                    'Expecting to see field "%s".', $fieldName
                ));
                return;
            }
            if (!$field->hasAttribute('disabled')) {
                throw $this->createExpectationException(sprintf(
                    'Expecting field "%s" to be disabled.', $fieldName
                ));
            }
        }
    }

    /**
     * @Given /^I fill in the following informations:$/
     */
    public function iFillInTheFollowingInformations(TableNode $table)
    {
        foreach ($table->getRowsHash() as $field => $value) {
            $this->getCurrentPage()->fillField($field, $value);
        }
    }

    /**
     * @Given /^I fill in the following fields:$/
     */
    public function iFillInTheFollowingFields(TableNode $table)
    {
        foreach ($table->getRowsHash() as $field => $value) {
            $this->getCurrentPage()->fillField($field, $value);
        }
    }

    /**
     * @Given /^I press the "([^"]*)" button$/
     */
    public function iPressTheButton($button)
    {
        $this->getCurrentPage()->pressButton($button);
        $this->wait(2000, '$(".alert-success .message").length > 0');
    }

    /**
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
        $this
            ->getPage('Product edit')
            ->disableProduct()
            ->save()
        ;
    }

    /**
     * @Given /^I enable the product$/
     */
    public function iEnableTheProduct()
    {
        $this
            ->getPage('Product edit')
            ->enableProduct()
            ->save()
        ;
    }

    /**
     * @Given /^product "([^"]*)" should be disabled$/
     */
    public function productShouldBeDisabled($sku)
    {
        if ($this->getProduct($sku)->isEnabled()) {
            throw $this->createExpectationException('Product was expected to be be disabled');
        }
    }

    /**
     * @Given /^product "([^"]*)" should be enabled$/
     */
    public function productShouldBeEnabled($sku)
    {
        if (!$this->getProduct($sku)->isEnabled()) {
            throw $this->createExpectationException('Product was expected to be be enabled');
        }
    }

    /**
     * @Then /^I should see channels (.*)$/
     */
    public function iShouldSeeChannels($channels)
    {
        $channels = $this->listToArray($channels);

        foreach ($channels as $channel) {
            if (!$this->getPage('Channel index')->findChannelRow($channel)) {
                throw $this->createExpectationException(
                    sprintf('Expecting to see channel %s, not found', $channel)
                );
            }
        }
    }

    /**
     * @Given /^the channel (.*) is able to export category (.*)$/
     */
    public function theChannelIsAbleToExportCategory($channel, $category)
    {
        if (!$this->getPage('Channel index')->channelCanExport($channel, $category)) {
            throw $this->createExpectationException(
                sprintf('Expecting channel %s to be able to export category %s', $channel, $category)
            );
        }
    }

    /**
     * @Given /^the channel (.*) is not able to export category (.*)$/
     */
    public function theChannelIsNotAbleToExportCategory($channel, $category)
    {
        if ($this->getPage('Channel index')->channelCanExport($channel, $category)) {
            throw $this->createExpectationException(
                sprintf('Expecting channel %s not to be able to export category %s', $channel, $category)
            );
        }
    }

    /**
     * @Then /^there should be (\d+) update$/
     */
    public function thereShouldBeUpdate($count)
    {
        if ((int) $count !== $countUpdates = $this->getPage('Product edit')->countUpdates()) {
            throw $this->createExpectationException(sprintf(
                'Expected %d updates, saw %d.', $count, $countUpdates
            ));
        }
    }

    private function openPage($page, array $options = array())
    {
        $this->currentPage = $page;

        return $this->getCurrentPage()->open($options);
    }

    private function getCurrentPage()
    {
        return $this->getPage($this->currentPage);
    }

    private function getInvalidValueFor($field)
    {
        switch ($field) {
            case 'Family edit.Code':
                return 'inv@lid';
        }
    }

    private function wait($time, $condition = null)
    {
        return $this->getMainContext()->wait($time, $condition);
    }

    private function getProduct($sku)
    {
        return $this->getFixturesContext()->getProduct($sku);
    }

    private function getGroup($name)
    {
        return $this->getFixturesContext()->getGroup($name);
    }

    private function getAttribute($type)
    {
        return $this->getFixturesContext()->getAttribute($type);
    }

    private function getFamily($code)
    {
        return $this->getFixturesContext()->getFamily($code);
    }

    private function getFixturesContext()
    {
        return $this->getMainContext()->getSubcontext('fixtures');
    }

    private function listToArray($list)
    {
        return $this->getMainContext()->listToArray($list);
    }

    private function getLocaleCode($language)
    {
        return $this->getFixturesContext()->getLocaleCode($language);
    }

    private function createExpectationException($message)
    {
        return $this->getMainContext()->createExpectationException($message);
    }
}
