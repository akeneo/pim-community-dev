<?php

namespace Context;

use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Exception\PendingException;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Role;

class WebUser extends PageObjectContext
{
    private $locales = array(
        'english' => 'en',
        'french'  => 'fr',
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
     * @Given /^a "([^"]*)" product with the following translations:$/
     */
    public function aProductWithTheFollowingTranslations($sku, TableNode $translations)
    {
        $attributes = array();
        $pm         = $this->getProductManager();
        $product    = $pm->createFlexible();
        $product->setSku($sku);

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
            $value->setLocale($this->locales[$translation['locale']]);
            $value->setData($translation['value']);
            $this->getProductManager()->getStorageManager()->persist($value);
            $product->addValue($value);
        }

        $this->getProductManager()->getStorageManager()->persist($product);
        $this->getProductManager()->getStorageManager()->flush();
    }

    /**
     * @Given /^the current language is (\w+)$/
     */
    public function theCurrentLanguageIs($language)
    {
        $this->currentLocale = $this->locales[$language];
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
     * @Then /^I should see that the product is available in french and english$/
     */
    public function iShouldSeeThatTheProductIsAvailableInFrenchAndEnglish()
    {
        throw new PendingException();
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
