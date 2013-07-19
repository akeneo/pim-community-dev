<?php

namespace Context;

use Doctrine\Common\Util\Inflector;
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
use Pim\Bundle\ProductBundle\Entity\ProductAttributeTranslation;
use Pim\Bundle\ProductBundle\Entity\ProductAttribute;
use Pim\Bundle\ProductBundle\Entity\Category;
use Pim\Bundle\ConfigBundle\Entity\Locale;
use Pim\Bundle\ConfigBundle\Entity\Channel;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixturesContext extends RawMinkContext
{
    private $locales = array(
        'english' => 'en_US',
        'french'  => 'fr_FR',
        'german'  => 'de',
    );

    private $attributeTypes = array(
        'text'       => 'pim_product_text',
        'number'     => 'pim_product_number',
        'textarea'   => 'pim_product_textarea',
        'identifier' => 'pim_product_identifier',
        'metric'     => 'pim_product_metric',
    );

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
     * @BeforeScenario
     */
    public function createRequiredAttribute()
    {
        $em = $this->getEntityManager();
        $attr = $this->createAttribute('SKU', false, 'identifier', true);
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
    public function resetAcl()
    {
        $root = new Acl();
        $root
            ->setId('root')
            ->setName('root')
            ->setDescription('root')
            ->addAccessRole($this->getRoleOrCreate(User::ROLE_DEFAULT))
            ->addAccessRole($this->getRoleOrCreate('ROLE_SUPER_ADMIN'));

        $oroSecurity = new Acl();
        $oroSecurity
            ->setId('oro_security')
            ->setName('Oro Security')
            ->setDescription('Oro security')
            ->setParent($root)
            ->addAccessRole($this->getRoleOrCreate('IS_AUTHENTICATED_ANONYMOUSLY'));

        $oroLogin = new Acl();
        $oroLogin
            ->setId('oro_login')
            ->setName('Login page')
            ->setDescription('Oro Login page')
            ->setParent($oroSecurity);

        $oroLoginCheck = new Acl();
        $oroLoginCheck
            ->setId('oro_login_check')
            ->setName('Login check')
            ->setDescription('Oro Login check')
            ->setParent($oroSecurity);

        $oroLogout = new Acl();
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
     * @Given /^the following products?:$/
     */
    public function theFollowingProduct(TableNode $table)
    {
        $pm = $this->getProductManager();
        foreach ($table->getHash() as $data) {
            $data = array_merge(array(
                'languages' => 'english',
                'family'    => null,
            ), $data);

            $product = $this->aProductAvailableIn($data['sku'], $data['languages']);
            if ($data['family']) {
                $product->setFamily($this->getFamily($data['family']));
            }

            $pm->save($product);
        }
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
     * @Given /^the following families:$/
     * @Given /^the following family:$/
     */
    public function theFollowingFamilies(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $data) {
            $family = new Family;
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
     * @Given /^the product "([^"]*)" belongs to the family "([^"]*)"$/
     */
    public function theProductBelongsToTheFamily($product, $family)
    {
        $product = $this->getProduct($product);
        $family  = $this->getFamily($family);

        $product->setFamily($family);
        $this->getEntityManager()->flush();
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
            $attribute->setUseableAsGridColumn(true);
            $attribute->isUseableAsGridFilter(true);

            if ($family = $data['family']) {
                $family = $this->getFamily($family);
                $family->addAttribute($attribute);
            }

            if ($data['type'] === 'metric') {
                if (!empty($data['metric family']) && !empty($data['default metric unit'])) {
                    $attribute->setMetricFamily($data['metric family']);
                    $attribute->setDefaultMetricUnit($data['default metric unit']);
                } else {
                    throw new \InvalidArgumentException(sprintf(
                        'Expecting metric family and default metric unit to be defined for attribute "%s"',
                        $data['label']
                    ));
                }
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
     * @Given /^the following categories:$/
     */
    public function theFollowingCategories(TableNode $table)
    {
        $em = $this->getEntityManager();
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

            $em->persist($category);
        }
        $em->flush();
    }

    /**
     * @Given /^the following channels:$/
     */
    public function theFollowingChannels(TableNode $table)
    {
        $em = $this->getEntityManager();
        foreach ($table->getHash() as $data) {
            $channel = new Channel();
            $channel->setCode($data['code']);
            $channel->setName($data['name']);

            if (isset($data['category'])) {
                $category = $this->getCategory($data['category']);
                $channel->setCategory($category);
            }
            $em->persist($channel);
        }
        $em->flush();
    }

    /**
     * @Given /^the following (\w+) "([^"]*)" updates:$/
     */
    public function theFollowingUpdates($entityName, $id, TableNode $table)
    {
        $entity = $this->{'get'.ucfirst($entityName)}($id);
        $em     = $this->getEntityManager();

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
            $audit->setData(array(
                $field => array(
                    'old' => $old,
                    'new' => $new,
                )
            ));
            $user = $this->getUser($data['updatedBy']);
            $audit->setUsername($user->getUsername());
            $audit->setUser($user);
            $em->persist($audit);
        }

        $em->flush();
    }


    private function getUser($username)
    {
        return $this->getEntityOrException('OroUserBundle:User', array(
            'username' => $username,
        ));
    }

    public function getProduct($sku)
    {
        $pm   = $this->getProductManager();
        $repo = $pm->getFlexibleRepository();
        $qb   = $repo->createQueryBuilder('p');
        $repo->applyFilterByAttribute($qb, 'sKU', $sku);
        $product = $qb->getQuery()->getOneOrNullResult();

        return $product ?: $this->createProduct($sku);
    }

    public function getOrCreateUser($username, $password = null, $apiKey = 'admin_api_key')
    {
        $em = $this->getEntityManager();

        if ($user = $em->getRepository('OroUserBundle:User')->findOneBy(array(
            'username' => $username,
        ))) {
            return $user;
        }

        $user = new User;

        if (!$password) {
            $password = $username.'pass';
        }

        $user->setUsername($username);
        $user->setEmail($username.'@example.com');
        $user->setPlainPassword($password);
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

        $api = new UserApi();
        $api->setApiKey($apiKey)->setUser($user);

        $this->getEntityManager()->persist($api);
        $this->getEntityManager()->flush();
    }

    public function getAttribute($label)
    {
        return $this->getEntityOrException('PimProductBundle:ProductAttribute', array(
            'code' => $this->camelize($label)
        ));
    }

    public function getLocaleCode($language)
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

    public function getGroup($name)
    {
        try {
            return $this->getEntityOrException('PimProductBundle:AttributeGroup', array(
                'code' => $this->camelize($name)
            ));
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    public function getFamily($code)
    {
        return $this->getEntityOrException('PimProductBundle:Family', array(
            'code' => $code
        ));
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

    private function createAttribute($label, $translatable = true, $type = 'text', $showInGrid = false)
    {
        $attribute = $this->getProductManager()->createAttribute($this->getAttributeType($type));
        $attribute->setCode($this->camelize($label));
        $attribute->setLabel($label);
        $attribute->setTranslatable($translatable);
        $attribute->setUseableAsGridColumn($showInGrid);
        $attribute->setUseableAsGridFilter($showInGrid);
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

    public function getCategory($code)
    {
        return $this->getEntityOrException('PimProductBundle:Category', array(
            'code' => $code,
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

    private function createFamilyTranslation(Family $family, $content, $locale = 'default')
    {
        $translation = new FamilyTranslation();
        $translation->setLabel($content);
        $translation->setLocale($locale);
        $translation->setForeignKey($family);

        $em = $this->getEntityManager();
        $em->persist($translation);
        $em->flush();

        return $translation;
    }

    private function camelize($string)
    {
        return Inflector::camelize(str_replace(' ', '_', $string));
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getMainContext()->getEntityManager();
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

    private function listToArray($list)
    {
        return $this->getMainContext()->listToArray($list);
    }
}
