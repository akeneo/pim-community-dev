<?php

namespace Context;

use Doctrine\Common\Util\Inflector;
use Doctrine\Common\Collections\ArrayCollection;
use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Gherkin\Node\TableNode;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\Role;
use Oro\Bundle\UserBundle\Entity\Acl;
use Oro\Bundle\UserBundle\Entity\UserApi;
use Oro\Bundle\DataAuditBundle\Entity\Audit;
use Pim\Bundle\ProductBundle\Entity\AttributeGroup;
use Pim\Bundle\ProductBundle\Entity\Family;
use Pim\Bundle\ProductBundle\Entity\FamilyTranslation;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\Category;
use Pim\Bundle\ProductBundle\Entity\ProductPrice;
use Pim\Bundle\ConfigBundle\Entity\Locale;
use Pim\Bundle\ConfigBundle\Entity\Channel;
use Pim\Bundle\BatchBundle\Entity\Job;

/**
 * A context for creating entities
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixturesContext extends RawMinkContext
{
    private $locales = array(
        'english' => 'en_US',
        'french'  => 'fr_FR',
        'german'  => 'de_DE',
    );

    private $channels = array(
        'ecommerce' => array('en_US', 'fr_FR'),
        'mobile'    => array('fr_FR'),
    );

    private $attributeTypes = array(
        'text'       => 'pim_product_text',
        'number'     => 'pim_product_number',
        'textarea'   => 'pim_product_textarea',
        'identifier' => 'pim_product_identifier',
        'metric'     => 'pim_product_metric',
        'prices'     => 'pim_product_price_collection',
        'image'      => 'pim_product_image',
        'file'       => 'pim_product_file',
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
    public function resetChannels()
    {
        foreach ($this->channels as $code => $locales) {
            $this->createChannel($code, $locales);
        }
    }

    /**
     * @BeforeScenario
     */
    public function createRequiredAttribute()
    {
        $this->createAttribute('SKU', false, 'identifier', true);
    }

    /**
     * @BeforeScenario
     */
    public function resetAcl()
    {
        $root        = $this->createAcl('root', null, array(User::ROLE_DEFAULT, 'ROLE_SUPER_ADMIN'));
        $oroSecurity = $this->createAcl('oro_security', $root, array('IS_AUTHENTICATED_ANONYMOUSLY'));

        $this->createAcl('oro_login', $oroSecurity);
        $this->createAcl('oro_login_check', $oroSecurity);
        $this->createAcl('oro_logout', $oroSecurity);
    }

    /**
     * @param string         $sku
     * @param TableNode|null $translations
     *
     * @return Product
     * @Given /^the "([^"]*)" product(?: has the following translations:)?$/
     */
    public function theProductWithTheFollowingTranslations($sku, TableNode $translations = null)
    {
        $attributes = array();
        $product    = $this->getProduct($sku);

        if ($translations) {
            foreach ($translations->getHash() as $translation) {
                if (isset($attributes[$translation['attribute']])) {
                    $attribute = $attributes[$translation['attribute']];
                } else {
                    $attribute = $this->createAttribute($translation['attribute'], true);
                    $attributes[$translation['attribute']] = $attribute;
                }

                $value = $this->createValue(
                    $attribute,
                    $translation['value'],
                    $this->getLocaleCode($translation['locale'])
                );
                $product->addValue($value);
            }
        }

        $this->getProductManager()->save($product);

        return $product;
    }

    /**
     * @param string    $sku
     * @param languages $languages
     *
     * @return Product
     * @Given /^a "([^"]*)" product available in (.*)$/
     */
    public function aProductAvailableIn($sku, $languages)
    {
        $product   = $this->theProductWithTheFollowingTranslations($sku);
        $languages = $this->listToArray($languages);

        foreach ($languages as $language) {
            $language = $this->getLocale($this->getLocaleCode($language));
            $locale = $product->getLocale($language);
            if (!$locale) {
                $product->addLocale($language);
            }
        }

        $this->getProductManager()->save($product);

        return $product;
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following products?:$/
     */
    public function theFollowingProduct(TableNode $table)
    {
        $pm = $this->getProductManager();
        foreach ($table->getHash() as $data) {
            $data = array_merge(array('languages' => 'english', 'family' => null), $data);

            $product = $this->aProductAvailableIn($data['sku'], $data['languages']);
            if ($data['family']) {
                $product->setFamily($this->getFamily($data['family']));
            }

            $pm->save($product);
        }
    }

    /**
     * @param string $sku
     *
     * @Given /^an enabled "([^"]*)" product$/
     */
    public function anEnabledProduct($sku)
    {
        $this->aProductAvailableIn($sku, 'english')->setEnabled(true);
    }

    /**
     * @param string $sku
     *
     * @Given /^a disabled "([^"]*)" product$/
     */
    public function aDisabledProduct($sku)
    {
        $this->aProductAvailableIn($sku, 'english')->setEnabled(false);
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following families:$/
     * @Given /^the following family:$/
     */
    public function theFollowingFamilies(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $family = new Family;
            $family->setCode($data['code']);
            $this->persist($family);

            $translation = $this->createFamilyTranslation($family, $data['code']);
            $family->addTranslation($translation);
        }

        $this->flush();
    }

    /**
     * @param string    $family
     * @param TableNode $table
     *
     * @Given /^the product family "([^"]*)" has the following attribute:$/
     */
    public function theFamilyHasTheFollowingAttribute($family, TableNode $table)
    {
        $family = $this->getFamily($family);

        foreach ($table->getHash() as $data) {
            $attribute = $this->getAttribute($data['label']);
            $family->addAttribute($attribute);
            if ('yes' === $data['attribute as label']) {
                $family->setAttributeAsLabel($attribute);
            }
        }

        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following family translations:$/
     */
    public function theFollowingFamilyTranslations(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $family      = $this->getFamily($data['family']);
            $translation = $this->createFamilyTranslation(
                $family,
                $data['label'],
                $this->getLocaleCode($data['language'])
            );

            $family->addTranslation($translation);
        }

        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following currencies:$/
     */
    public function theFollowingCurrencies(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $currency = new \Pim\Bundle\ConfigBundle\Entity\Currency;
            $currency->setCode($data['code']);
            $currency->setActivated($data['activated'] === 'yes');

            $this->persist($currency);
        }
    }

    /**
     * @Given /^the following locales:$/
     */
    public function theFollowingLocales(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $data) {
            $locale = $em->getRepository('PimConfigBundle:Locale')->findOneBy(array('code' => $data['code']));
            if (!$locale) {
                $locale = new Locale();
                $locale->setCode($data['code']);
            }

            $locale->setFallback($data['fallback']);
            $locale->setActivated($data['activated'] === 'yes');

            $em->persist($locale);
        }
        $em->flush();
    }

    /**
     * @param string $product
     * @param string $family
     *
     * @Given /^the product "([^"]*)" belongs to the family "([^"]*)"$/
     */
    public function theProductBelongsToTheFamily($product, $family)
    {
        $product = $this->getProduct($product);
        $family  = $this->getFamily($family);

        $product->setFamily($family);
        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attribute groups?:$/
     */
    public function theFollowingAttributeGroups(TableNode $table)
    {
        foreach ($table->getHash() as $index => $data) {
            $group = new AttributeGroup();
            $group->setCode($this->camelize($data['name']));
            $group->setName($data['name']);
            $group->setSortOrder($index);

            $this->persist($group);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product attributes?:$/
     */
    public function theFollowingProductAttributes(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $data = array_merge(
                array(
                    'position'     => 0,
                    'group'        => null,
                    'product'      => null,
                    'family'       => null,
                    'required'     => 'no',
                    'type'         => 'text',
                    'scopable'     => false,
                    'scopable'     => 'no',
                    'translatable' => 'no',
                ),
                $data
            );

            try {
                $attribute = $this->getAttribute($data['label']);
            } catch (\InvalidArgumentException $e) {
                $attribute = $this->createAttribute($data['label'], false, $data['type']);
            }

            $attribute->setSortOrder($data['position']);
            $attribute->setGroup($this->getGroup($data['group']));
            $attribute->setRequired(strtolower($data['required']) === 'yes');
            $attribute->setScopable(strtolower($data['scopable']) === 'yes');
            $attribute->setTranslatable(strtolower($data['translatable']) === 'yes');
            $attribute->setUseableAsGridColumn(true);
            $attribute->setUseableAsGridFilter(true);

            if ($family = $data['family']) {
                $family = $this->getFamily($family);
                $family->addAttribute($attribute);
            }

            if ($data['type'] === 'metric') {
                if (!empty($data['metric family']) && !empty($data['default metric unit'])) {
                    $attribute->setMetricFamily($data['metric family']);
                    $attribute->setDefaultMetricUnit($data['default metric unit']);
                } else {
                    throw new \InvalidArgumentException(
                        sprintf(
                            'Expecting metric family and default metric unit to be defined for attribute "%s"',
                            $data['label']
                        )
                    );
                }
            }

            if (!empty($data['product'])) {
                $product = $this->getProduct($data['product']);
                $value   = $this->createValue($attribute);

                $product->addValue($value);
                $this->getProductManager()->save($product);
            }
        }

        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following product values?:$/
     */
    public function theFollowingProductValue(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $data = array_merge(array('scope' => null, 'locale' => null), $data);
            $data['locale']= (empty($data['locale'])) ? null : $data['locale'];
            $data['scope']= (empty($data['scope'])) ? null : $data['scope'];

            $product = $this->getProduct($data['product']);
            $value   = $product->getValue($this->camelize($data['attribute']), $data['locale'], $data['scope']);

            if ($value) {
                if ($data['scope']) {
                    $value->setScope($data['scope']);
                }
                if ($data['locale']) {
                    $value->setLocale($data['locale']);
                }
                if ($data['value']) {
                    if ($value->getAttribute()->getAttributeType() == $this->attributeTypes['prices']) {
                        foreach ($value->getPrices() as $price) {
                            $value->removePrice($price);
                        }
                        $prices = $this->createPricesFromString($data['value']);
                        foreach ($prices as $price) {
                            $value->addPrice($price);
                        }
                    } else {
                        $value->setData($data['value']);
                    }
                }
            } else {
                $attribute = $this->getAttribute($data['attribute']);
                $value = $this->createValue($attribute, $data['value'], $data['locale'], $data['scope']);
                $product->addValue($value);
            }
        }

        $this->flush();
    }

    /**
     * @param string $attribute
     * @param string $family
     *
     * @Given /^the attribute "([^"]*)" has been removed from the "([^"]*)" family$/
     */
    public function theAttributeHasBeenRemovedFromTheFamily($attribute, $family)
    {
        $attribute = $this->getAttribute($attribute);
        $family    = $this->getFamily($family);

        $family->removeAttribute($attribute);

        $this->flush();
    }

    /**
     * @param string $attribute
     * @param string $family
     *
     * @Given /^the attribute "([^"]*)" has been chosen as the family "([^"]*)" label$/
     */
    public function theAttributeHasBeenChosenAsTheFamilyLabel($attribute, $family)
    {
        $attribute = $this->getAttribute($attribute);
        $family    = $this->getFamily($family);

        $family->setAttributeAsLabel($attribute);

        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following categor(?:y|ies):$/
     */
    public function theFollowingCategories(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $category = new Category();
            $category->setCode($data['code']);
            $category->setTitle($data['title']);

            if (isset($data['products'])) {
                $skus = explode(',', $data['products']);
                foreach ($skus as $sku) {
                    $category->addProduct($this->getProduct($sku));
                }
            }

            $this->persist($category);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following channels:$/
     */
    public function theFollowingChannels(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $channel = new Channel();
            $channel->setCode($data['code']);
            $channel->setName($data['name']);

            if (isset($data['category'])) {
                $category = $this->getCategory($data['category']);
                $channel->setCategory($category);
            }
            $this->persist($channel);
        }
    }

    /**
     * @param string    $entityName
     * @param string    $id
     * @param TableNode $table
     *
     * @Given /^the following (\w+) "([^"]*)" updates:$/
     */
    public function theFollowingUpdates($entityName, $id, TableNode $table)
    {
        $entity = $this->{'get'.ucfirst($entityName)}($id);

        foreach ($table->getHash() as $data) {
            $audit = new Audit;
            $audit->setAction($data['action']);
            $audit->setLoggedAt(new \DateTime($data['loggedAt']));
            $audit->setObjectId($entity->getId());
            $audit->setObjectClass(get_class($entity));
            $audit->setObjectName((string) $entity);
            $audit->setVersion(1);
            list($field, $change) = explode(': ', $data['change']);
            list($old, $new) = explode(' => ', $change);
            $audit->setData(array($field => array('old' => $old, 'new' => $new)));
            $user = $this->getUser($data['updatedBy']);
            $audit->setUsername($user->getUsername());
            $audit->setUser($user);
            $this->persist($audit);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following jobs?:$/
     */
    public function theFollowingJobs(TableNode $table)
    {
        $registry = $this->getContainer()->get('pim_batch.connectors');

        foreach ($table->getHash() as $data) {
            $job = new Job($data['connector'], $data['type'], $data['alias']);
            $job->setCode($data['code']);
            $job->setLabel($data['label']);

            $jobDefinition = $registry->getJob($job);
            $job->setJobDefinition($jobDefinition);

            $this->persist($job);
        }
    }

    /**
     * @param string    $code
     * @param TableNode $table
     *
     * @Given /^the following job "([^"]*)" configuration:$/
     */
    public function theFollowingJobConfiguration($code, TableNode $table)
    {
        $registry      = $this->getContainer()->get('pim_batch.connectors');
        $job           = $this->getJob($code);
        $jobDefinition = $registry->getJob($job);
        $steps         = $jobDefinition->getSteps();

        foreach ($table->getHash() as $data) {
            $config[$data['element']][$data['property']] = $data['value'];
        }
        $config = array_merge(array('reader' => array(), 'processor' => array(), 'writer' => array()), $config);
        $steps[0]->setConfiguration($config);
        $job->setJobDefinition($jobDefinition);

        $this->flush();
    }

    /**
     * @Given /^there is no identifier attribute$/
     */
    public function thereIsNoIdentifierAttribute()
    {
        $attributes = $this->getRepository('PimProductBundle:ProductAttribute')
                ->findBy(array('attributeType' => 'pim_product_identifier'));

        foreach ($attributes as $attribute) {
            $this->remove($attribute);
        }
    }

    /**
     * @param string $username
     *
     * @return User
     */
    private function getUser($username)
    {
        return $this->getEntityOrException('OroUserBundle:User', array('username' => $username));
    }

    /**
     * @param string $sku
     *
     * @return Product
     */
    public function getProduct($sku)
    {
        $pm   = $this->getProductManager();
        $repo = $pm->getFlexibleRepository();
        $qb   = $repo->createQueryBuilder('p');
        $repo->applyFilterByAttribute($qb, $pm->getIdentifierAttribute()->getCode(), $sku);
        $product = $qb->getQuery()->getOneOrNullResult();

        return $product ?: $this->createProduct($sku);
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $apiKey
     *
     * @return User
     */
    public function getOrCreateUser($username, $password = null, $apiKey = null)
    {
        if ($user = $this->getRepository('OroUserBundle:User')->findOneBy(array('username' => $username))) {
            return $user;
        }

        return $this->createUser($username, $password, $apiKey);
    }

    /**
     * @param string $label
     *
     * @return ProductAttribute
     */
    public function getAttribute($label)
    {
        return $this->getEntityOrException(
            'PimProductBundle:ProductAttribute',
            array(
                'code' => $this->camelize($label)
            )
        );
    }

    /**
     * @param string $language
     *
     * @return string
     */
    public function getLocaleCode($language)
    {
        if ('default' === $language) {
            return $language;
        }

        if (!isset($this->locales[$language])) {
            throw new \InvalidArgumentException(sprintf('Undefined language "%s"', $language));
        }

        return $this->locales[$language];
    }

    /**
     * @param string $name
     *
     * @return AttributeGroup|null
     */
    public function getGroup($name)
    {
        try {
            return $this->getEntityOrException(
                'PimProductBundle:AttributeGroup',
                array(
                    'code' => $this->camelize($name)
                )
            );
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    /**
     * @param string $code
     *
     * @return Family
     */
    public function getFamily($code)
    {
        return $this->getEntityOrException('PimProductBundle:Family', array('code' => $code));
    }

    /**
     * @param mixed $data
     *
     * @return Product
     */
    private function createProduct($data)
    {
        $product = $this->getProductManager()->createFlexible();
        $sku     = $this->getAttribute('SKU');
        $value   = $this->createValue($sku, $data);

        $product->addValue($value);
        $this->persist($product);

        return $product;
    }

    /**
     * @param string  $label
     * @param boolean $translatable
     * @param string  $type
     * @param boolean $showInGrid
     *
     * @return ProductAttribute
     */
    private function createAttribute($label, $translatable = true, $type = 'text', $showInGrid = false)
    {
        $attribute = $this->getProductManager()->createAttribute($this->getAttributeType($type));
        $attribute->setCode($this->camelize($label));
        $attribute->setLabel($label);
        $attribute->setTranslatable($translatable);
        $attribute->setUseableAsGridColumn($showInGrid);
        $attribute->setUseableAsGridFilter($showInGrid);
        $this->persist($attribute);

        return $attribute;
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private function getAttributeType($type)
    {
        return isset($this->attributeTypes[$type]) ? $this->attributeTypes[$type] : null;
    }

    /**
     * @param ProductAttribute $attribute
     * @param mixed            $data
     * @param string           $locale
     * @param string           $scope
     *
     * @return ProductValue
     */
    private function createValue(ProductAttribute $attribute, $data = null, $locale = null, $scope = null)
    {
        $pm = $this->getProductManager();

        $value = $pm->createFlexibleValue();
        $value->setAttribute($attribute);
        if ($attribute->getAttributeType() == $this->attributeTypes['prices']) {
            $prices = $this->createPricesFromString($data);
            foreach ($prices as $price) {
                $value->addPrice($price);
            }
        } else {
            $value->setData($data);
        }
        $value->setLocale($locale);
        $value->setScope($scope);

        return $value;
    }

    /**
     * @param string $code
     *
     * @return Locale
     */
    private function getLocale($code)
    {
        try {
            $lang = $this->getEntityOrException('PimConfigBundle:Locale', array('code' => $code));
        } catch (\InvalidArgumentException $e) {
            $this->createLocale($code);
        }

        return $lang;
    }

    /**
     * @param string $code
     *
     * @return Locale
     */
    private function createLocale($code)
    {
        $locale = new Locale;
        $locale->setCode($code);

        $this->persist($locale);
    }

    /**
     * @param string       $code
     * @param array:string $locales
     *
     * @return Channel
     */
    private function createChannel($code, $locales)
    {
        $channel = new Channel;
        $channel->setCode($code);
        $channel->setName(ucfirst($code));

        foreach ($locales as $localeCode) {
            $channel->addLocale($this->getLocale($localeCode));
        }

        $this->persist($channel);
    }

    /**
     * @param string $label
     *
     * @return Role
     */
    private function getRoleOrCreate($label)
    {
        try {
            $role = $this->getEntityOrException('OroUserBundle:Role', array('label' => $label));
        } catch (\InvalidArgumentException $e) {
            $role = new Role($label);
            $this->persist($role);
        }

        return $role;
    }

    /**
     * @param string $code
     *
     * @return Category
     */
    public function getCategory($code)
    {
        return $this->getEntityOrException('PimProductBundle:Category', array('code' => $code));
    }

    /**
     * @param string $code
     *
     * @return Job
     */
    public function getJob($code)
    {
        return $this->getEntityOrException('PimBatchBundle:Job', array('code' => $code));
    }

    /**
     * @param string $namespace
     * @param array  $criteria
     *
     * @return object
     */
    private function getEntityOrException($namespace, array $criteria)
    {
        $entity = $this->getRepository($namespace)->findOneBy($criteria);

        if (!$entity) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find "%s" with criteria %s',
                    $namespace,
                    print_r($criteria, true)
                )
            );
        }

        return $entity;
    }

    /**
     * @param Family $family
     * @param string $content
     * @param string $locale
     *
     * @return FamilyTranslation
     */
    private function createFamilyTranslation(Family $family, $content, $locale = 'default')
    {
        $translation = new FamilyTranslation();
        $translation->setLabel($content);
        $translation->setLocale($locale);
        $translation->setForeignKey($family);

        $this->persist($translation);

        return $translation;
    }

    /**
     * @param string $prices
     *
     * @return ArrayCollection:Price
     */
    private function createPricesFromString($prices)
    {
        $prices = explode(',', $prices);
        $data = array();

        foreach ($prices as $price) {
            $price = explode(' ', trim($price));
            $amount = array_filter(
                $price,
                function ($item) {
                    return preg_match('/^[0-9]+(\.[0-9]+)?$/', $item);
                }
            );
            $amount = reset($amount);
            if (!$amount) {
                continue;
            }
            $currency = array_filter(
                $price,
                function ($item) {
                    return preg_match('/^[a-zA-Z]+(.+)$/', $item);
                }
            );
            $currency = !empty($currency) ? reset($currency) : 'EUR';
            $data[] = $this->createPrice($amount, $currency);
        }

        return new ArrayCollection($data);
    }

    /**
     * @param string $data
     * @param string $currency
     *
     * @return ProductPrice
     */
    private function createPrice($data, $currency = 'EUR')
    {
        $price = new ProductPrice();
        $price->setData($data);
        $price->setCurrency($currency);

        return $price;
    }

    /**
     * @param string $username
     * @param strng  $password
     * @param string $apiKey
     *
     * @return User
     */
    private function createUser($username, $password = null, $apiKey = null)
    {
        $password = $password ?: $username . 'pass';
        $apiKey   = $apiKey ?: $username . '_api_key';
        $email    = $username.'@example.com';
        $locale   = 'en_US';
        $scope    = 'ecommerce';

        $user = new User();
        $user->setUsername($username);
        $user->setPlainPassword($password);
        $user->setEmail($email);

        $user->addRole($this->getRoleOrCreate(User::ROLE_DEFAULT));
        $user->addRole($this->getRoleOrCreate(User::ROLE_ANONYMOUS));

        $manager = $this->getContainer()->get('oro_user.manager.flexible');

        $localeAttribute = $manager->createAttribute('oro_flexibleentity_text');
        $localeAttribute->setCode('cataloglocale')->setLabel('cataloglocale');
        $this->persist($localeAttribute);

        $localeValue = $manager->createFlexibleValue();
        $localeValue->setAttribute($localeAttribute);
        $localeValue->setData($locale);
        $user->addValue($localeValue);

        $scopeAttribute = $manager->createAttribute('oro_flexibleentity_text');
        $scopeAttribute->setCode('catalogscope')->setLabel('catalogscope');
        $this->persist($scopeAttribute);

        $scopeValue = $manager->createFlexibleValue();
        $scopeValue->setAttribute($scopeAttribute);
        $scopeValue->setData($scope);
        $user->addValue($scopeValue);

        $this->getUserManager()->updateUser($user);

        $api = new UserApi();
        $api->setApiKey($username.'_api_key')->setUser($user);

        $this->persist($api);

        return $user;
    }

    /**
     * @param string $name
     * @param Acl    $parent
     * @param array  $roles
     *
     * @return Acl
     */
    private function createAcl($name, $parent = null, array $roles = array())
    {
        $acl = new Acl();
        $acl->setId($name);
        $acl->setName($this->camelize($name));
        $acl->setDescription($this->camelize($name));
        if ($parent) {
            $acl->setParent($parent);
        }
        foreach ($roles as $role) {
            $acl->addAccessRole($this->getRoleOrCreate($role));
        }
        $this->persist($acl);

        return $acl;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function camelize($string)
    {
        return Inflector::camelize(str_replace(' ', '_', strtolower($string)));
    }

    /**
     * Persist an entity
     *
     * @param object  $entity
     * @param boolean $flush
     */
    private function persist($entity, $flush = true)
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Remove an entity
     *
     * @param object  $entity
     * @param boolean $flush
     */
    private function remove($entity, $flush = true)
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->flush();
        }
    }

    /**
     * Flush
     */
    private function flush()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getMainContext()->getEntityManager();
    }

    /**
     * @param string $repository
     *
     * @return Repository
     */
    private function getRepository($repository)
    {
        return $this->getEntityManager()->getRepository($repository);
    }

    /**
     * @return \Pim\Bundle\ProductBundle\Manager\ProductManager
     */
    private function getProductManager()
    {
        return $this->getContainer()->get('pim_product.manager.product');
    }

    /**
     * @return \Oro\Bundle\UserBundle\Entity\UserManager
     */
    private function getUserManager()
    {
        return $this->getContainer()->get('oro_user.manager');
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private function getContainer()
    {
        return $this->getMainContext()->getContainer();
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
}
