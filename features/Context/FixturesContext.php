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
use Pim\Bundle\CatalogBundle\Entity\AttributeGroup;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Entity\FamilyTranslation;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;
use Pim\Bundle\CatalogBundle\Entity\Category;
use Pim\Bundle\CatalogBundle\Entity\ProductPrice;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\BatchBundle\Entity\JobInstance;

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
        'text'       => 'pim_catalog_text',
        'number'     => 'pim_catalog_number',
        'textarea'   => 'pim_catalog_textarea',
        'identifier' => 'pim_catalog_identifier',
        'metric'     => 'pim_catalog_metric',
        'prices'     => 'pim_catalog_price_collection',
        'image'      => 'pim_catalog_image',
        'file'       => 'pim_catalog_file',
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

        $this->flush();
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
     * @BeforeScenario
     */
    public function clearUOW()
    {
        $this->getEntityManager()->clear();
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
     * @param string $sku
     *
     * @return \Pim\Bundle\CatalogBundle\Entity\Product
     *
     * @Given /^a "([^"]*)" product$/
     */
    public function aProduct($sku)
    {
        $product   = $this->theProductWithTheFollowingTranslations($sku);
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
        foreach ($table->getHash() as $data) {
            $data = array_merge(array('family' => null), $data);

            $product = $this->aProduct($data['sku']);
            if ($data['family']) {
                $product->setFamily($this->getFamily($data['family']));
            }

            if (isset($data['enabled'])) {
                $product->setEnabled($data['enabled'] === 'yes');
            }

            $this->getProductManager()->save($product);
        }
    }

    /**
     * @param string $sku
     *
     * @Given /^an enabled "([^"]*)" product$/
     */
    public function anEnabledProduct($sku)
    {
        $this->aProduct($sku)->setEnabled(true);
    }

    /**
     * @param string $sku
     *
     * @Given /^a disabled "([^"]*)" product$/
     */
    public function aDisabledProduct($sku)
    {
        $this->aProduct($sku)->setEnabled(false);
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
            $family = new Family();
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
     * @Given /^the family "([^"]*)" has the following attribute:$/
     */
    public function theFamilyHasTheFollowingAttribute($family, TableNode $table)
    {
        $family = $this->getFamily($family);

        foreach ($table->getHash() as $data) {
            $code = $this->camelize($data['label']);
            $attribute = $this->getAttribute($code);
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
     * @Given /^the following currencies:$/
     */
    public function theFollowingCurrencies(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $currency = new \Pim\Bundle\CatalogBundle\Entity\Currency;
            $currency->setCode($data['code']);
            $currency->setActivated($data['activated'] === 'yes');

            $this->persist($currency);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following locales:$/
     */
    public function theFollowingLocales(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $data) {
            $locale = $em->getRepository('PimCatalogBundle:Locale')->findOneBy(array('code' => $data['code']));
            if (!$locale) {
                $locale = new Locale();
                $locale->setCode($data['code']);
            }

            $locale->setFallback($data['fallback']);
            if ($data['activated'] === 'yes') {
                $locale->activate();
            }

            $em->persist($locale);
        }
        $em->flush();
    }

    /**
     * @Given /^there is no channel$/
     */
    public function thereIsNoChannel()
    {
        $em = $this->getEntityManager();
        $channels = $em->getRepository('PimCatalogBundle:Channel')->findAll();

        foreach ($channels as $channel) {
            $this->remove($channel);
        }
        $this->flush();
    }

    /**
     * @Given /^there is no attribute$/
     */
    public function thereIsNoAttribute()
    {
        $em = $this->getEntityManager();
        $attributes = $em->getRepository('PimCatalogBundle:ProductAttribute')->findAll();

        foreach ($attributes as $attribute) {
            $this->remove($attribute);
        }
        $this->flush();
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
            $group->setLocale('en_US')->setName($data['name']); // TODO translation refactoring
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
                $code = $this->camelize($data['label']);
                $attribute = $this->getAttribute($code);
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
                $code = $this->camelize($data['attribute']);
                $attribute = $this->getAttribute($code);
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
        $code      = $this->camelize($attribute);
        $attribute = $this->getAttribute($code);
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
        $code      = $this->camelize($attribute);
        $attribute = $this->getAttribute($code);
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
            $category->setLocale('en_US')->setTitle($data['title']); // TODO translation refactoring

            if (!empty($data['parent'])) {
                $parent = $this->getCategoryOrCreate($data['parent']);
                $category->setParent($parent);
            }

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

            if (isset($data['locales'])) {
                $locales = explode(', ', $data['locales']);
                foreach ($locales as $localeCode) {
                    $locale = $this->getLocale($localeCode);
                    $channel->addLocale($locale);
                }
            }

            if (isset($data['category'])) {
                $category = $this->getCategory($data['category']);
                $channel->setCategory($category);
            }

            if (isset($data['currencies'])) {
                foreach ($data['currencies'] as $currencyCode) {
                    $currency = $this->getCurrency($currencyCode);
                    $channel->addCurrency($currency);
                }
            }

            $this->persist($channel);
        }
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attributes:$/
     */
    public function theFollowingAttributes(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $attribute = $this->getProductManager()->createAttribute($data['type']);

            $attribute->setCode($data['code']);
            $attribute->setScopable($data['scopable'] === 'yes');
            $attribute->setTranslatable($data['localizable'] === 'yes');

            $attribute->setLocale('en_US');
            $attribute->setLabel($data['label']);

            $group = $this->getGroup($data['group']);
            $attribute->setGroup($group);

            $this->persist($attribute);
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
            $jobInstance = new JobInstance($data['connector'], $data['type'], $data['alias']);
            $jobInstance->setCode($data['code']);
            $jobInstance->setLabel($data['label']);

            $job = $registry->getJob($jobInstance);
            $jobInstance->setJob($job);

            $this->persist($jobInstance);
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
        $registry    = $this->getContainer()->get('pim_batch.connectors');
        $jobInstance = $this->getJobInstance($code);
        $job         = $registry->getJob($jobInstance);
        $steps       = $job->getSteps();

        foreach ($table->getHash() as $data) {
            $config[$data['element']][$data['property']] = $data['value'];
        }
        $config = array_merge(array('reader' => array(), 'processor' => array(), 'writer' => array()), $config);
        $steps[0]->setConfiguration($config);
        $jobInstance->setJob($job);

        $this->flush();
    }

    /**
     * @param TableNode $table
     *
     * @Given /^the following attribute requirements:$/
     */
    public function theFollowingAttributeRequirements(TableNode $table)
    {
        foreach ($table->getHash() as $data) {
            $requirement = new AttributeRequirement();

            $attribute = $this->getAttribute($data['attribute']);
            $channel   = $this->getChannel($data['scope']);
            $family    = $this->getFamily($data['family']);

            $requirement->setAttribute($attribute);
            $requirement->setChannel($channel);
            $requirement->setFamily($family);

            $requirement->setRequired($data['required'] === 'yes');

            $this->persist($requirement);
        }

        $this->flush();
    }

    /**
     * @Given /^there is no identifier attribute$/
     */
    public function thereIsNoIdentifierAttribute()
    {
        $attributes = $this->getRepository('PimCatalogBundle:ProductAttribute')
                ->findBy(array('attributeType' => 'pim_catalog_identifier'));

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
        $manager    = $this->getProductManager();
        $repository = $manager->getFlexibleRepository();
        $qb         = $repository->createQueryBuilder('p');
        $repository->applyFilterByAttribute($qb, $manager->getIdentifierAttribute()->getCode(), $sku);
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
     * @param string $code
     *
     * @return ProductAttribute
     */
    public function getAttribute($code)
    {
        return $this->getEntityOrException(
            'PimCatalogBundle:ProductAttribute',
            array(
                'code' => $code
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
                'PimCatalogBundle:AttributeGroup',
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
        return $this->getEntityOrException('PimCatalogBundle:Family', array('code' => $code));
    }

    /**
     * @param string $code
     *
     * @return Channel
     */
    public function getChannel($code)
    {
        return $this->getEntityOrException('PimCatalogBundle:Channel', array('code' => $code));
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
        $attribute->setLocale('en_US')->setLabel($label); //TODO translation refactoring
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
        $manager = $this->getProductManager();

        $value = $manager->createFlexibleValue();
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
            $lang = $this->getEntityOrException('PimCatalogBundle:Locale', array('code' => $code));
        } catch (\InvalidArgumentException $e) {
            $this->createLocale($code);
        }

        return $lang;
    }

    /**
     * Get currency
     *
     * @param string $code
     *
     * @return Currency
     */
    public function getCurrency($code)
    {
        return $this->getEntityOrException('PimCatalogBundle:Currency', array('code' => $code));
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
        return $this->getEntityOrException('PimCatalogBundle:Category', array('code' => $code));
    }

    /**
     * @param string $code
     *
     * @return Category
     */
    private function getCategoryOrCreate($code)
    {
        try {
            $category = $this->getCategory($code);
        } catch (\InvalidArgumentException $e) {
            $category = new Category();
            $category->setCode($code);
            $this->persist($category);
        }

        return $category;
    }

    /**
     * @param string $code
     *
     * @return Job
     */
    public function getJobInstance($code)
    {
        return $this->getEntityOrException('PimBatchBundle:JobInstance', array('code' => $code));
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
    private function createFamilyTranslation(Family $family, $content, $locale = 'en_US')
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
     * @return \Pim\Bundle\CatalogBundle\Manager\ProductManager
     */
    private function getProductManager()
    {
        return $this->getContainer()->get('pim_catalog.manager.product');
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
