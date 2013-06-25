<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Exception\PendingException;
use Behat\Mink\Exception\ExpectationException;

use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;

use Doctrine\Common\Util\Inflector;

use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Role;

use Pim\Bundle\ProductBundle\Entity\AttributeGroup;
use Pim\Bundle\ProductBundle\Entity\ProductFamily;
use Pim\Bundle\ProductBundle\Entity\ProductFamilyTranslation;
use Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ConfigBundle\Entity\Locale;

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

    private $locales = array(
        'english' => 'en_US',
        'french'  => 'fr_FR',
        'german'  => 'de',
    );

    private $currentPage = null;

    private $attributeTypes = array(
        'text'     => 'pim_product_text',
        'number'   => 'pim_product_number',
        'textarea' => 'pim_product_textarea',
    );

    /**
     * @BeforeScenario
     */
    public function createRequiredAttribute()
    {
        $em = $this->getEntityManager();
        $attr = $this->createAttribute('SKU', false);
        $em->persist($attr);
        $em->flush();
    }

    /**
     * @BeforeScenario
     */
    public function resetCurrentLocale()
    {
        foreach ($this->locales as $locale) {
            $this->createLocale($locale);
        }
    }

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
    public function resetAcl()
    {
        $root = new \Oro\Bundle\UserBundle\Entity\Acl;
        $root
            ->setId('root')
            ->setName('root')
            ->setDescription('root')
            ->addAccessRole($this->getRoleOrCreate(User::ROLE_DEFAULT))
            ->addAccessRole($this->getRoleOrCreate('ROLE_SUPER_ADMIN'));

        $oroSecurity = new \Oro\Bundle\UserBundle\Entity\Acl;
        $oroSecurity
            ->setId('oro_security')
            ->setName('Oro Security')
            ->setDescription('Oro security')
            ->setParent($root)
            ->addAccessRole($this->getRoleOrCreate('IS_AUTHENTICATED_ANONYMOUSLY'));

        $oroLogin = new \Oro\Bundle\UserBundle\Entity\Acl();
        $oroLogin
            ->setId('oro_login')
            ->setName('Login page')
            ->setDescription('Oro Login page')
            ->setParent($oroSecurity);

        $oroLoginCheck = new \Oro\Bundle\UserBundle\Entity\Acl();
        $oroLoginCheck
            ->setId('oro_login_check')
            ->setName('Login check')
            ->setDescription('Oro Login check')
            ->setParent($oroSecurity);

        $oroLogout = new \Oro\Bundle\UserBundle\Entity\Acl();
        $oroLogout
            ->setId('oro_logout')
            ->setName('Logout')
            ->setDescription('Oro Logout')
            ->setParent($oroSecurity);

        $em = $this->getEntityManager();
        $em->persist($root);
        $em->persist($oroSecurity);
        $em->persist($oroLogin);
        $em->persist($oroLoginCheck);
        $em->persist($oroLogout);
        $em->flush();
    }

    /**
     * @BeforeScenario
     */
    public function resetChannels()
    {
        $channel = new \Pim\Bundle\ConfigBundle\Entity\Channel;
        $channel->setCode('ecommerce');
        $channel->setName('ecommerce');

        $em = $this->getEntityManager();
        $em->persist($channel);
        $em->flush();
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
     * @Given /^the "([^"]*)" product(?: has the following translations:)?$/
     */
    public function theProductWithTheFollowingTranslations($sku, TableNode $translations = null)
    {
        $attributes = array();
        $product    = $this->getProduct($sku);
        $pm         = $this->getProductManager();

        if ($translations) {
            foreach ($translations->getHash() as $translation) {
                if (isset($attributes[$translation['attribute']])) {
                    $attribute = $attributes[$translation['attribute']];
                } else {
                    $attribute = $this->createAttribute($translation['attribute'], true);
                    $pm->getStorageManager()->persist($attribute);
                    $attributes[$translation['attribute']] = $attribute;
                }

                $value = $this->createValue($attribute, $translation['value'], $this->getLocaleCode($translation['locale']));
                $product->addValue($value);
            }
        }
        $pm->save($product);

        return $product;
    }

    /**
     * @Given /^a "([^"]*)" product available in (.*)$/
     */
    public function aProductAvailableIn($sku, $languages)
    {
        $product   = $this->theProductWithTheFollowingTranslations($sku);
        $languages = $this->listToArray($languages);

        foreach ($languages as $language) {
            $language = $this->getLocale($this->getLocaleCode($language));
            $pl = $product->getLocale($language);
            if (!$pl) {
                $product->addLocale($language);
            }
            $this->getProductManager()->save($product);
        }

        $this->getEntityManager()->flush();

        return $product;
    }

    /**
     * @Given /^the following product:$/
     */
    public function theFollowingProduct(TableNode $table)
    {
        $pm = $this->getProductManager();
        foreach ($table->getHash() as $data) {
            $data = array_merge(array(
                'languages' => 'english',
            ), $data);

            $product = $this->aProductAvailableIn($data['sku'], $data['languages']);
            $product->setProductFamily($this->getFamily($data['family']));
            $pm->save($product);
        }
    }

    /**
     * @Given /^the following families:$/
     * @Given /^the following family:$/
     */
    public function theFollowingFamilies(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $data) {
            $family = new ProductFamily;
            $family->setCode($data['code']);
            $em->persist($family);

            $translation = $this->createFamilyTranslation($family, $data['code']);
            $family->addTranslation($translation);
        }

        $em->flush();
    }

    /**
     * @Given /^the product family "([^"]*)" has the following attribute:$/
     */
    public function theProductFamilyHasTheFollowingAttribute($family, TableNode $table)
    {
        $family = $this->getFamily($family);

        foreach ($table->getHash() as $data) {
            $attribute = $this->getAttribute($data['label']);
            $family->addAttribute($attribute);
            if ('yes' === $data['attribute as label']) {
                $family->setAttributeAsLabel($attribute);
            }
        }

        $this->getEntityManager()->flush();
    }

    /**
     * @Given /^the following family translations:$/
     */
    public function theFollowingFamilyTranslations(TableNode $table)
    {
        $em = $this->getEntityManager();

        foreach ($table->getHash() as $data) {
            $family      = $this->getFamily($data['family']);
            $translation = $this->createFamilyTranslation(
                $family, $data['label'], $this->getLocaleCode($data['language'])
            );

            $family->addTranslation($translation);
        }

        $em->flush();
    }

    /**
     * @Given /^the following currencies:$/
     */
    public function theFollowingCurrencies(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $data) {
            $currency = new \Pim\Bundle\ConfigBundle\Entity\Currency;
            $currency->setCode($data['code']);
            $currency->setActivated($data['activated'] === 'yes');

            $em->persist($currency);
        }
        $em->flush();
    }


    /**
     * @Given /^the product "([^"]*)" belongs to the family "([^"]*)"$/
     */
    public function theProductBelongsToTheFamily($product, $family)
    {
        $product = $this->getProduct($product);
        $family  = $this->getFamily($family);

        $product->setProductFamily($family);
        $this->getEntityManager()->flush();
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
        $this->getPage('Product edit')->switchLocale($this->getLocaleCode($locale));
    }

    /**
     * @Then /^the locale switcher should contain the following items:$/
     */
    public function theLocaleSwitcherShouldContainTheFollowingItems(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            if (!$this->getPage('Product edit')->findLocale($data['locale'], $data['label'])) {
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
        $em   = $this->getEntityManager();
        $user = new User;

        $user->setUsername($username);
        $user->setEmail($username.'@example.com');
        $user->setPlainPassword($password = $username.'pass');
        $user->addRole($this->getRoleOrCreate(User::ROLE_DEFAULT));
        $user->addRole($this->getRoleOrCreate(User::ROLE_ANONYMOUS));

        $um = $this->getContainer()->get('oro_user.manager.flexible');
        $catalogLocaleAttribute = $um->createAttribute('oro_flexibleentity_text');
        $catalogLocaleAttribute->setCode('cataloglocale');
        $catalogLocaleAttribute->setLabel('cataloglocale');
        $em->persist($catalogLocaleAttribute);

        $catalogLocaleValue = $um->createFlexibleValue();
        $catalogLocaleValue->setAttribute($catalogLocaleAttribute);
        $catalogLocaleValue->setData('en_US');
        $user->addValue($catalogLocaleValue);

        $um = $this->getContainer()->get('oro_user.manager.flexible');
        $catalogScopeAttribute = $um->createAttribute('oro_flexibleentity_text');
        $catalogScopeAttribute->setCode('catalogscope');
        $catalogScopeAttribute->setLabel('catalogscope');
        $em->persist($catalogScopeAttribute);

        $catalogScopeValue = $um->createFlexibleValue();
        $catalogScopeValue->setAttribute($catalogScopeAttribute);
        $catalogScopeValue->setData('ecommerce');
        $user->addValue($catalogScopeValue);

        $this->getUserManager()->updateUser($user);

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
     * @Given /^the following attribute groups?:$/
     */
    public function theFollowingAttributeGroups(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $index => $data) {
            $group = new AttributeGroup();
            $group->setCode($this->camelize($data['name']));
            $group->setName($data['name']);
            $group->setSortOrder($index);

            $em->persist($group);
        }
        $em->flush();
    }

    /**
     * @Given /^the following product attributes?:$/
     */
    public function theFollowingProductAttributes(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $index => $data) {
            $data = array_merge(array(
                'position' => 0,
                'group'    => null,
                'product'  => null,
                'family'   => null,
                'required' => 'no',
                'type'     => 'text',
            ), $data);

            try {
                $attribute = $this->getAttribute($data['label']);
            } catch (\InvalidArgumentException $e) {
                $attribute = $this->createAttribute($data['label'], false, $data['type']);
            }
            $attribute->setSortOrder($data['position']);
            $attribute->setGroup($this->getGroup($data['group']));
            $attribute->setRequired($data['required'] === 'yes');

            if ($family = $data['family']) {
                $family = $this->getFamily($family);
                $family->addAttribute($attribute);
            }

            if (!empty($data['product'])) {
                $product = $this->getProduct($data['product']);
                $value   = $this->createValue($attribute);

                $product->addValue($value);
                $this->getProductManager()->save($product);
            }
        }
        $em->flush();
    }

    /**
     * @Given /^the following product values?:$/
     */
    public function theFollowingProductValue(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $data) {
            $data = array_merge(array(
                'scope' => null,
            ), $data);

            $product = $this->getProduct($data['product']);
            $value   = $product->getValue($this->camelize($data['attribute']));

            if ($value) {
                if (false === $value->getScope()) {
                    $value->setScope($data['scope']);
                }
                if (null === $value->getData()) {
                    $value->setData($data['value']);
                }
            } else {
                $attribute = $this->getAttribute($data['attribute']);
                $value = $this->createValue($attribute, $data['value'], null, $data['scope']);
                $product->addValue($value);
            }
        }
        $em->flush();
    }

    /**
     * @Then /^I should see that the product is available in (.*)$/
     */
    public function iShouldSeeLanguages($languages)
    {
        $languages = $this->listToArray($languages);
        foreach ($languages as $language) {
            if (null === $this->getPage('Product edit')->findLocaleLink($this->getLocaleCode($language))) {
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
        $attributes = $this->listToArray($attributes);
        $group = $this->getGroup($group) ?: 'Other';

        if (count($attributes) !== $actual = $this->getPage('Product edit')->getFieldsCountFor($group)) {
            throw $this->createExpectationException(sprintf(
                'Expected to see %d fields in group "%s", actually saw %d',
                count($attributes), $group, $actual
            ));
        }

        foreach ($attributes as $index => $attribute) {
            $field = $this
                ->getPage('Product edit')
                ->getFieldAt($group, $index)
            ;

            if ($attribute !== $name = $field->getText()) {
                throw new \Exception(sprintf('
                    Expecting to see field "%s" at position %d, but saw "%s"',
                    $attribute, $index + 1, $name
                ));
            }
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
     * @Given /^the attribute "([^"]*)" has been removed from the "([^"]*)" family$/
     */
    public function theAttributeHasBeenRemovedFromTheFamily($attribute, $family)
    {
        $attribute = $this->getAttribute($attribute);
        $family    = $this->getFamily($family);

        $family->removeAttribute($attribute);

        $this->getEntityManager()->flush();
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
     * @Given /^I add available attributes (.*)$/
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
     * @Given /^the attribute "([^"]*)" has been chosen as the family "([^"]*)" label$/
     */
    public function theAttributeHasBeenChosenAsTheFamilyLabel($attribute, $family)
    {
        $attribute = $this->getAttribute($attribute);
        $family    = $this->getFamily($family);

        $family->setAttributeAsLabel($attribute);

        $this->getEntityManager()->flush();
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
     * @Given /^I fill in the following informations:$/
     */
    public function iFillInTheFollowingInformations(TableNode $table)
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
     * @Given /^an enabled "([^"]*)" product$/
     */
    public function anEnabledProduct($sku)
    {
        $this
            ->aProductAvailableIn($sku, 'english')
            ->setEnabled(true)
        ;

        $this->getEntityManager()->flush();
    }

    /**
     * @Given /^a disabled "([^"]*)" product$/
     */
    public function aDisabledProduct($sku)
    {
        $this
            ->aProductAvailableIn($sku, 'english')
            ->setEnabled(false)
        ;

        $this->getEntityManager()->flush();
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

    private function openPage($page, array $options = array())
    {
        $this->currentPage = $page;

        return $this->getCurrentPage()->open($options);
    }

    private function getCurrentPage()
    {
        return $this->getPage($this->currentPage);
    }

    private function listToArray($list)
    {
        return explode(', ', str_replace(' and ', ', ', $list));
    }

    private function getProduct($sku)
    {
        $pm   = $this->getProductManager();
        $repo = $pm->getFlexibleRepository();
        $qb   = $repo->createQueryBuilder('p');
        $repo->applyFilterByAttribute($qb, 'sKU', $sku);
        $product = $qb->getQuery()->getOneOrNullResult();

        return $product ?: $this->createProduct($sku);
    }

    private function createProduct($data)
    {
        $product = $this->getProductManager()->createFlexible();
        $sku     = $this->getAttribute('SKU');
        $value   = $this->createValue($sku, $data);

        $product->addValue($value);
        $this->getProductManager()->getStorageManager()->persist($product);
        $this->getProductManager()->getStorageManager()->flush();

        return $product;
    }

    private function createAttribute($label, $translatable = true, $type = 'text')
    {
        $attribute = $this->getProductManager()->createAttribute($this->getAttributeType($type));
        $attribute->setCode($this->camelize($label));
        $attribute->setLabel($label);
        $attribute->setTranslatable($translatable);

        $translation = new ProductAttributeTranslation();
        $translation->setContent($label);
        $translation->setField('label');
        $translation->setForeignKey($attribute);
        $translation->setLocale('default');
        $translation->setObjectClass('Pim\Bundle\ProductBundle\Entity\ProductAttribute');

        $attribute->addTranslation($translation);
        $this->getProductManager()->getStorageManager()->persist($attribute);

        return $attribute;
    }

    private function getAttributeType($type)
    {
        return isset($this->attributeTypes[$type]) ? $this->attributeTypes[$type] : null;
    }

    private function createValue(ProductAttribute $attribute, $data = null, $locale = null, $scope = null)
    {
        $pm = $this->getProductManager();

        $value = $pm->createFlexibleValue();
        $value->setAttribute($attribute);
        $value->setData($data);
        $value->setLocale($locale);

        return $value;
    }

    private function getLocaleCode($language)
    {
        if ('default' === $language) {
            return $language;
        }

        if (!isset($this->locales[$language])) {
            throw new \InvalidArgumentException(sprintf(
                'Undefined language "%s"', $language
            ));
        }

        return $this->locales[$language];
    }

    private function getLocale($code)
    {
        try {
            $lang = $this->getEntityOrException('PimConfigBundle:Locale', array(
                'code' => $code
            ));
        } catch (\InvalidArgumentException $e) {
            $this->createLocale($code);
        }

        return $lang;
    }

    private function createLocale($code)
    {
        $locale = new Locale;
        $locale->setCode($code);

        $em = $this->getEntityManager();
        $em->persist($locale);
        $em->flush();
    }

    private function getGroup($name)
    {
        try {
            return $this->getEntityOrException('PimProductBundle:AttributeGroup', array(
                'name' => $name
            ));
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function getAttribute($label)
    {
        return $this->getEntityOrException('PimProductBundle:ProductAttribute', array(
            'label' => $label
        ));
    }

    private function getFamily($code)
    {
        return $this->getEntityOrException('PimProductBundle:ProductFamily', array(
            'code' => $code
        ));
    }

    private function getRoleOrCreate($label)
    {
        try {
            $role = $this->getEntityOrException('OroUserBundle:Role', array(
                'label' => $label
            ));
        } catch (\InvalidArgumentException $e) {
            $role = new Role($label);
            $em = $this->getEntityManager();
            $em->persist($role);
            $em->flush();
        }

        return $role;
    }

    private function getEntityOrException($namespace, array $criteria)
    {
        $entity = $this
            ->getEntityManager()
            ->getRepository($namespace)
            ->findOneBy($criteria)
        ;

        if (!$entity) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find "%s" with criteria %s', $namespace, print_r($criteria, true)
            ));
        }

        return $entity;
    }

    private function createFamilyTranslation(ProductFamily $family, $content, $locale = 'default')
    {
        $translation = new ProductFamilyTranslation();
        $translation->setContent($content);
        $translation->setField('label');
        $translation->setLocale($locale);
        $translation->setObjectClass('Pim\Bundle\ProductBundle\Entity\ProductFamily');
        $translation->setForeignKey($family);

        $em = $this->getEntityManager();
        $em->persist($translation);
        $em->flush();

        return $translation;
    }

    private function getInvalidValueFor($field)
    {
        switch ($field) {
            case 'Family edit.Code':
                return 'inv@lid';
        }
    }

    private function getProductManager()
    {
        return $this->getContainer()->get('pim_product.manager.product');
    }

    private function getUserManager()
    {
        return $this->getContainer()->get('oro_user.manager');
    }

    private function getEntityManager()
    {
        return $this->getMainContext()->getEntityManager();
    }

    private function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }

    private function camelize($string)
    {
        return Inflector::camelize(str_replace(' ', '_', $string));
    }

    private function createExpectationException($message)
    {
        return new ExpectationException($message, $this->getSession());
    }

    private function wait($time, $condition = null)
    {
        $this->getSession()->wait($time, $condition);
    }
}
