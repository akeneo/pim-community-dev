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
        'french'  => 'fr',
        'german'  => 'de',
    );

    private $currentLocale = null;

    /**
     * @BeforeScenario
     */
    public function resetCurrentLocale()
    {
        $this->currentLocale = null;
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

        $this->getPage('Login')->open(array('locale' => $this->currentLocale))->login($username, $password);
    }

    /**
     * @Given /^I am on the "([^"]*)" product page$/
     */
    public function iAmOnTheProductPage($product)
    {
        $product = $this->getProduct($product);
        $this->getPage('Product')->open(array(
            'locale' => $this->currentLocale,
            'id'     => $product->getId(),
        ));
    }

    /**
     * @When /^I am on the "([^"]*)" attribute page$/
     */
    public function iAmOnTheAttributePage($code)
    {
        $attribute = $this->getAttribute($code);
        $this->getPage('Attribute')->open(array(
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
            ), $data);
            $attribute = $this->createAttribute($data['code'], false);
            $attribute->getAttribute()->setSortOrder($data['position']);
            $attribute->setGroup($this->getGroup($data['group']));
            $product = $this->getProduct($data['product']);

                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attribute->getAttribute());
                $value->setData(null);
                $this->getProductManager()->getStorageManager()->persist($value);

                $product->addValue($value);
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
     * @Given /^attributes in group "([^"]*)" should be (.*)$/
     */
    public function attributesInGroupShouldBe($group, $attributes)
    {
        $attributes = $this->listToArray($attributes);
        $group = $this->getGroup($group);
        foreach ($attributes as $index => $attribute) {
            $field = $this
                ->getPage('Product')
                ->getFieldAt($group, $index)
            ;

            if (strtolower($attribute) !== $name = strtolower($field->getText())) {
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
        $actual = $this->getPage('Product')->getFieldValue($fieldName);

        if ($expected !== $actual) {
            throw new \LogicException(sprintf(
                'Expected product %s to be "%s", but got "%s".',
                $fieldName, $expected, $actual
            ));
        }
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

        if (!$product) {
            $product = $this->getProductManager()->createFlexible();
            $product->setSku($sku);
            $pm->getStorageManager()->persist($product);
        }

        return $product;
    }

    private function createAttribute($code, $translatable = true)
    {
        $attribute = $this->getProductManager()->createAttributeExtended(new TextType);
        $attribute->setCode(strtolower($code));
        $attribute->setName(ucfirst($code));
        $attribute->setTranslatable($translatable);
        $this->getProductManager()->getStorageManager()->persist($attribute);

        return $attribute;
    }

    private function getLocale($language)
    {
        if (!isset($this->locales[$language])) {
            throw new \InvalidArgumentException(sprintf(
                'Undefined language "%s"', $language
            ));
        }

        return $this->locales[$language];
    }

    private function getLanguage($code)
    {
        $em = $this->getEntityManager();
        $lang = $em->getRepository('PimConfigBundle:Language')->findOneBy(array(
            'code' => $code
        ));

        if (!$lang) {
            $lang = new Language;
            $lang->setCode($code);
            $em->persist($lang);
        }

        return $lang;
    }

    private function getGroup($name)
    {
        $em    = $this->getEntityManager();
        $group = $em->getRepository('PimProductBundle:AttributeGroup')->findOneBy(array(
            'name' => $name
        ));

        if (!$group) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find group with name "%s"', $name
            ));
        }

        return $group;
    }

    private function getAttribute($name)
    {
        $em    = $this->getEntityManager();
        $attribute = $em->getRepository('PimProductBundle:ProductAttribute')->findOneBy(array(
            'name' => ucfirst($name)
        ));

        if (!$attribute) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find attribute with name "%s"', ucfirst($name)
            ));
        }

        return $attribute;
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
}
