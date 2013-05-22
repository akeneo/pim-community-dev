<?php

namespace Context;

use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Exception\PendingException;

use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Role;

use Pim\Bundle\ConfigBundle\Entity\Language;
use Behat\MinkExtension\Context\RawMinkContext;
use SensioLabs\Behat\PageObjectExtension\Context\PageObjectAwareInterface;
use SensioLabs\Behat\PageObjectExtension\Context\PageFactory;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;
use Doctrine\Common\Util\Inflector;
use Pim\Bundle\ProductBundle\Entity\ProductFamily;
use Behat\Mink\Exception\ExpectationException;
use Pim\Bundle\ProductBundle\Entity\ProductFamilyTranslation;
use Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation;

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

    private $currentLocale = null;

    private $currentPage = null;

    /**
     * @BeforeScenario
     */
    public function resetCurrentLocale()
    {
        foreach ($this->locales as $locale) {
            $this->createLanguage($locale);
        }
        $this->currentLocale = null;
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
                    $attribute = $pm->createAttribute(new TextType);
                    $attribute->setCode($translation['attribute']);
                    $attribute->setTranslatable(true);
                    $pm->getStorageManager()->persist($attribute);
                    $attributes[$translation['attribute']] = $attribute;
                }

                $value = $pm->createFlexibleValue();
                $value->setAttribute($attribute);
                $value->setLocale($this->getLocale($translation['locale']));
                $value->setData($translation['value']);
                $pm->getStorageManager()->persist($value);
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
            $language = $this->getLanguage($this->getLocale($language));
            $pl = $product->getLanguage($language);
            if (!$pl) {
                $product->addLanguage($language, true);
            }
        }

        $this->getEntityManager()->flush();
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
                $family, $data['label'], $this->getLocale($data['language'])
            );

            $family->addTranslation($translation);
        }

        $em->flush();
    }

    /**
     * @Given /^the current language is (\w+)$/
     */
    public function theCurrentLanguageIs($language)
    {
        $this->currentLocale = $this->getLocale($language);
    }

    /**
     * @When /^I switch the locale to "([^"]*)"$/
     */
    public function iSwitchTheLocaleTo($locale)
    {
        $this->getPage('Product')->switchLocale($locale);
    }

    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($username)
    {
        $user = new User;
        $role = new Role(User::ROLE_DEFAULT);

        $user->setUsername($username);
        $user->setEmail($username.'@example.com');
        $user->setPlainPassword($password = $username.'pass');
        $user->addRole($role);

        $this->getEntityManager()->persist($role);
        $this->getUserManager()->updateUser($user);

        $this
            ->openPage('Login', array('locale' => $this->currentLocale))
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
            'locale' => $this->currentLocale,
            'id'     => $product->getId(),
        ));
    }

    /**
     * @When /^I am on the "([^"]*)" attribute page$/
     */
    public function iAmOnTheAttributePage($label)
    {
        $attribute = $this->getAttribute($label);

        $this->openPage('Attribute', array(
            'locale' => $this->currentLocale,
            'id'     => $attribute->getId(),
        ));
    }

    /**
     * @Given /^availabe languages are (.*)$/
     */
    public function availabeLanguagesAre($languages)
    {
        $languages = $this->listToArray($languages);
        $em        = $this->getEntityManager();
        $products  = $em->getRepository('PimProductBundle:Product')->findAll();
        $langs     = array();

        foreach ($languages as $language) {
            $langs[] = $this->getLanguage($this->getLocale($language));
        }

        foreach ($products as $product) {
            foreach ($langs as $lang) {
                $pl = $product->getLanguage($lang);
                if (!$pl) {
                    $product->addLanguage($lang);
                }
            }
        }

        $em->flush();
    }

    /**
     * @Given /^I visit the "([^"]*)" tab$/
     */
    public function iVisitTheTab($tab)
    {
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

                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attribute);
                $value->setData(null);
                $pm = $this->getProductManager();

                $pm->getStorageManager()->persist($value);

                $product->addValue($value);
                $pm->save($product);
            }
        }
        $em->flush();
    }

    /**
     * @Given /^the following product:$/
     */
    public function theFollowingProduct(TableNode $table)
    {
        $pm = $this->getProductManager();
        foreach ($table->getHash() as $data) {
            $product = $this->createProduct($data['sku']);
            $product->setProductFamily($this->getProductFamily($data['family']));
            $pm->save($product);
        }
    }

    /**
     * @Given /^the following attributes:$/
     */
    public function theFollowingAttributes(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $data) {
            $attribute = $this->createAttribute($data['label'], false);
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
            $this
                ->getPage('Product')
                ->setAssertSession($this->assertSession())
                ->assertLocaleIsDisplayed($language)
            ;
        }

    }

    /**
     * @When /^I select (.*) languages$/
     */
    public function iSelectLanguages($languages)
    {
        $languages = $this->listToArray($languages);
        foreach ($languages as $language) {
            $this
                ->getPage('Product')
                ->selectLanguage($this->getLocale($language))
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
        $group = $this->getGroup($group);
        foreach ($attributes as $index => $attribute) {
            $field = $this
                ->getPage('Product')
                ->getFieldAt($group ?: 'Other', $index)
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
     * @Then /^the product (.*) should be empty$/
     * @Then /^the product (.*) should be "([^"]*)"$/
     */
    public function theProductFieldValueShouldBe($fieldName, $expected = '')
    {
        $actual = $this->getSession()->getPage()->findField($field)->getValue();

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
        try {
            $field = $language ? $this->getPage($this->currentPage)->getFieldLocator(
                $field, $this->getLocale($language)
            ) : $field;
        } catch (\BadMethodCallException $e) {
            // Use default $field if current page does not provide a getFieldLocator method
        }

        return $this->getSession()->getPage()->fillField(
            $field, $value ?: $this->getInvalidValueFor(sprintf('%s.%s', $this->currentPage, $field))
        );
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
     * @When /^I am on family page$/
     */
    public function iAmOnFamilyPage()
    {
        $this->openPage('Family index', array(
            'locale' => $this->currentLocale,
        ));
    }

    /**
     * @When /^I am on the family creation page$/
     */
    public function iAmOnTheFamilyCreationPage()
    {
        $this->openPage('Family creation', array(
            'locale' => $this->currentLocale,
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
     * @Given /^I am on the "([^"]*)" family page$/
     */
    public function iAmOnTheFamilyPage($family)
    {
        $this->openPage('Family edit', array(
            'locale'    => $this->currentLocale,
            'family_id' => $this->getFamily($family)->getId()
        ));
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
            'locale'    => $this->currentLocale,
            'family_id' => $this->getFamily($family)->getId(),
        ));
        $this->assertSession()->addressEquals($expectedAddress);
    }

    private function openPage($page, array $options)
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
            ->setLocale($this->currentLocale)
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

    private function createAttribute($label, $translatable = true)
    {
        $attribute = $this->getProductManager()->createAttribute('oro_flexibleentity_text');
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

    private function getLocale($language)
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

    public function getProductFamily($code)
    {
        return $this->getEntityOrException('PimProductBundle:ProductFamily', array(
            'code' => $code
        ));
    }

    /**
     * @Then /^I should not see a remove link next to the "([^"]*)" field$/
     */
    public function iShouldNotSeeARemoveLinkNextToTheField($field)
    {
        if ($this->getPage('Product')->getRemoveLinkFor($field)) {
            throw $this->createExpectationException(sprintf(
                'Remove link on field "%s" should not be displayed.', $field
            ));
        }
    }

    /**
     * @When /^I remove the "([^"]*)" attribute$/
     */
    public function iRemoveTheAttribute($field)
    {
        if (null === $link = $this->getPage('Product')->getRemoveLinkFor($field)) {
            throw $this->createExpectationException(sprintf(
                'Remove link on field "%s" should be displayed.', $field
            ));
        }

        $link->click();
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
        sleep(5); //TODO Find a way to wait for the page to be loaded
    }

    private function getLanguage($code)
    {
        try {
            $lang = $this->getEntityOrException('PimConfigBundle:Language', array(
                'code' => $code
            ));
        } catch (\InvalidArgumentException $e) {
            $this->createLanguage($code);
        }

        return $lang;
    }

    private function createLanguage($code)
    {
        $lang = new Language;
        $lang->setCode($code);

        $em = $this->getEntityManager();
        $em->persist($lang);
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
