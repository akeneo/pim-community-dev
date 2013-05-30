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
        'text'     => 'oro_flexibleentity_text',
        'number'   => 'oro_flexibleentity_number',
        'textarea' => 'pim_product_textarea',
    );

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
                $product->addLocale($language, true);
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
            $product->setProductFamily($this->getProductFamily($data['family']));
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
     * @Then /^I should see activated currencies (.*)$/
     */
    public function iShouldSeeActivatedCurrencies($currencies)
    {
        foreach ($this->listToArray($currencies) as $currency) {
            if (!$this->getPage('Currency index')->findActivatedCurrency($currency)) {
                throw $this->createExpectationException(sprintf(
                    'Activated currency "%s" is not displayed', $currency
                ));
            }
        }
    }

    /**
     * @Given /^I should see deactivated currency (.*)$/
     */
    public function iShouldSeeDeactivatedCurrencies($currencies)
    {
        foreach ($this->listToArray($currencies) as $currency) {
            if (!$this->getPage('Currency index')->findDeactivatedCurrency($currency)) {
                throw $this->createExpectationException(sprintf(
                    'Activated currency "%s" is not displayed', $currency
                ));
            }
        }
    }

    /**
     * @When /^I switch the locale to "([^"]*)"$/
     */
    public function iSwitchTheLocaleTo($locale)
    {
        $this->getPage('Product')->switchLocale($this->getLocaleCode($locale));
    }

    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $em   = $this->getEntityManager();
        $user = new User;
        $role = new Role(User::ROLE_DEFAULT);

        $user->setUsername($username);
        $user->setEmail($username.'@example.com');
        $user->setPlainPassword($password = $username.'pass');
        $user->addRole($role);

        $um = $this->getContainer()->get('oro_user.manager.flexible');
        $catalogLocaleAttribute = $um->createAttribute('oro_flexibleentity_text');
        $catalogLocaleAttribute->setCode('cataloglocale');
        $catalogLocaleAttribute->setLabel('cataloglocale');
        $em->persist($catalogLocaleAttribute);

        $catalogLocaleValue = $um->createFlexibleValue();
        $catalogLocaleValue->setAttribute($catalogLocaleAttribute);
        $catalogLocaleValue->setData('en_US');
        $user->addValue($catalogLocaleValue);

        $this->getEntityManager()->persist($role);
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
        $product           = $this->getProduct($product);
        $this->openPage('Product', array(
            'id' => $product->getId(),
        ));
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
        $this->getSession()->wait(5000, '$("table.grid tbody tr").length > 0');
    }

    /**
     * @Given /^I visit the "([^"]*)" tab$/
     */
    public function iVisitTheTab($tab)
    {
        $this->getPage($this->currentPage)->visitTab($tab);
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
            ), $data);

            $attribute = $this->createAttribute($data['label'], false);
            $attribute->setSortOrder($data['position']);
            $attribute->setGroup($this->getGroup($data['group']));

            if ($family = $data['family']) {
                $family = $this->getProductFamily($family);
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
     * @Given /^the following attributes:$/
     */
    public function theFollowingAttributes(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $data) {
            $data = array_merge(array(
                'group' => null,
                'type'  => 'text',
            ), $data);

            $attribute = $this->createAttribute($data['label'], false, $data['type']);
            $attribute->setGroup($this->getGroup($data['group']));

            if (isset($data['family']) && $data['family']) {
                $this->getFamily($data['family'])->addAttribute($attribute);
            }

            $em->persist($attribute);
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
            if (null === $this->getPage('Product')->findLocaleLink($this->getLocaleCode($language))) {
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
                ->getPage('Product')
                ->selectLanguage($language)
            ;
        }
    }

    /**
     * @Given /^I save the product$/
     */
    public function iSaveTheProduct()
    {
        $this->getPage('Product')->save();
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

        if (count($attributes) !== $actual = $this->getPage('Product')->getFieldsCountFor($group)) {
            throw $this->createExpectationException(sprintf(
                'Expected to see %d fields in group "%s", actually saw %d',
                count($attributes), $group, $actual
            ));
        }

        foreach ($attributes as $index => $attribute) {
            $field = $this
                ->getPage('Product')
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
        if ($title !== $actual = $this->getPage('Product')->getTitle()) {
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
        $actual = $this->getPage('Product')->findField(ucfirst($fieldName))->getValue();

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
                $field = $this->getPage($this->currentPage)->getFieldLocator(
                    $field, $this->getLocaleCode($language)
                );
            } catch (\BadMethodCallException $e) {
                // Use default $field if current page does not provide a getFieldLocator method
            }
        } else {
            $field = ucfirst($field);
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
            $element = $this->getPage($this->currentPage)->getAvailableAttribute($attribute, $group);
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
        foreach ($this->listToArray($attributes) as $attribute) {
            $this->getPage($this->currentPage)->selectAvailableAttribute($attribute);
        }

        $this->getPage($this->currentPage)->addSelectedAvailableAttributes();
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
        if (!$this->getPage($this->currentPage)->getAttribute($attribute, $group)) {
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
        $removeLink = $this->getPage('Product')->getRemoveLinkFor($field);
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
        if (null === $link = $this->getPage($this->currentPage)->getRemoveLinkFor($field)) {
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

    private function openPage($page, array $options = array())
    {
        $this->currentPage = $page;

        return $this->getPage($page)->open($options);
    }

    private function listToArray($list)
    {
        return explode(', ', str_replace(' and ', ', ', $list));
    }

    private function getProduct($sku)
    {
        $pm = $this->getProductManager();
        $product = $pm
            ->getFlexibleRepository()
            ->findOneBy(array(
                'sku' => $sku,
            ));

        return $product ?: $this->createProduct($sku);
    }

    private function createProduct($sku)
    {
        $product = $this->getProductManager()->createFlexible();
        $product->setSku($sku);
        $this->getProductManager()->getStorageManager()->persist($product);

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

        $pm->getStorageManager()->persist($value);

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

    private function getProductFamily($code)
    {
        return $this->getEntityOrException('PimProductBundle:ProductFamily', array(
            'code' => $code
        ));
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
            'label' => ucfirst($label)
        ));
    }

    private function getFamily($code)
    {
        return $this->getEntityOrException('PimProductBundle:ProductFamily', array(
            'code' => $code
        ));
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
        return $this->getContainer()->get('product_manager');
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
}
