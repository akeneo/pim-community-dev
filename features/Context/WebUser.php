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
        'english' => 'en',
        'french'  => 'fr',
        'german'  => 'de',
    );

    private $currentLocale;

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
     * @Given /^a "([^"]*)" product(?: with the following translations:)?$/
     */
    public function aProductWithTheFollowingTranslations($sku, TableNode $translations = null)
    {
        $attributes = array();
        $pm         = $this->getProductManager();
        $product    = $pm->createFlexible();
        $product->setSku($sku);

        if ($translations) {
            foreach ($translations->getHash() as $translation) {
                if (isset($attributes[$translation['attribute']])) {
                    $attribute = $attributes[$translation['attribute']];
                } else {
                    $attribute = $this->getProductManager()->createAttribute(new TextType);
                    $attribute->setCode($translation['attribute']);
                    $attribute->setTranslatable(true);
                    $this->getProductManager()->getStorageManager()->persist($attribute);
                    $attributes[$translation['attribute']] = $attribute;
                }

                $value = $this->getProductManager()->createFlexibleValue();
                $value->setAttribute($attribute);
                $value->setLocale($this->getLocale($translation['locale']));
                $value->setData($translation['value']);
                $this->getProductManager()->getStorageManager()->persist($value);
                $product->addValue($value);
            }
        }

        $this->getProductManager()->getStorageManager()->persist($product);
        $this->getProductManager()->getStorageManager()->flush();

        return $product;
    }

    /**
     * @Given /^a "([^"]*)" product available in (.*)$/
     */
    public function aProductAvailableIn($sku, $languages)
    {
        $product   = $this->aProductWithTheFollowingTranslations($sku);
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
     * @Given /^I press "([^"]*)"$/
     */
    public function iPress($button)
    {
        $this->getPage('Product')->save();
    }

    /**
     * @Then /^I should see that the product is available in french and english$/
     */
    public function iShouldSeeThatTheProductIsAvailableInFrenchAndEnglish()
    {
        throw new PendingException();
    }

    private function listToArray($list)
    {
        return explode(', ', str_replace(' and ', ', ', $list));
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

    private function getProduct($sku)
    {
        $product = $this
            ->getProductManager()
            ->setLocale($this->currentLocale)
            ->getFlexibleRepository()
            ->findOneBy(array(
                'sku' => $sku,
            ));

        if (!$product) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find product with sku "%s"', $sku
            ));
        }

        return $product;
    }
}
