<?php

namespace Context;

use SensioLabs\Behat\PageObjectExtension\Context\PageObjectContext;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Exception\PendingException;
use Oro\Bundle\FlexibleEntityBundle\Model\AttributeType\TextType;

class ProductContext extends PageObjectContext
{
    private $locales = array(
        'english' => 'en_US',
        'french'  => 'fr_FR',
    );

    private $language;

    /**
     * @BeforeScenario
     */
    public function resetLanguage()
    {
        $this->language = null;
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
        $this->language = $language;
    }

    /**
     * @Given /^I am logged in as "([^"]*)"$/
     */
    public function iAmLoggedInAs($arg1)
    {
        throw new PendingException();
    }

    /**
     * @Given /^I am on the "([^"]*)" product page$/
     */
    public function iAmOnTheProductPage($arg1)
    {
        throw new PendingException();
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

    private function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }
}
