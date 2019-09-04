<?php

namespace Pim\Behat\Context;

use Akeneo\Channel\Component\Model\Channel;
use Akeneo\Channel\Component\Model\Currency;
use Akeneo\Channel\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Category\Model\Category;
use Akeneo\Pim\Enrichment\Component\Product\Model\Group;
use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\Model\AssociationType;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Pim\Structure\Component\Model\AttributeOption;
use Akeneo\Pim\Structure\Component\Model\Family;
use Akeneo\Pim\Structure\Component\Model\FamilyVariant;
use Akeneo\Pim\Structure\Component\Model\GroupType;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\UserManagement\Component\Model\Group as UserGroup;
use Akeneo\UserManagement\Component\Model\Role;
use Akeneo\UserManagement\Component\Model\User;
use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Util\Debug;
use Doctrine\Common\Util\Inflector;
use Doctrine\DBAL\Driver\Connection;
use PHPUnit\Framework\Assert;

/**
 * A context for creating entities
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixturesContext extends PimContext
{
    use SpinCapableTrait;

    protected $entities = [
        'Attribute'        => Attribute::class,
        'AttributeGroup'   => AttributeGroup::class,
        'AttributeOption'  => AttributeOption::class,
        'Channel'          => Channel::class,
        'Currency'         => Currency::class,
        'Family'           => Family::class,
        'FamilyVariant'    => FamilyVariant::class,
        'Category'         => Category::class, // TODO: To remove
        'ProductCategory'  => Category::class,
        'AssociationType'  => AssociationType::class,
        'JobInstance'      => JobInstance::class,
        'JobConfiguration' => 'Akeneo\Tool\Component\Connector\Model\JobConfiguration',
        'User'             => User::class,
        'Role'             => Role::class,
        'UserGroup'        => UserGroup::class,
        'Locale'           => Locale::class,
        'GroupType'        => GroupType::class,
        'Product'          => Product::class,
        'ProductGroup'     => Group::class,
    ];

    /**
     * Magic methods for getting and creating entities
     *
     * @param string $method
     * @param array  $args
     *
     * @throws \BadMethodCallException
     *
     * @return mixed
     */
    public function __call($method, $args)
    {
        if ('getOrCreate' === $getter = substr($method, 0, 11)) {
            $entityName = substr($method, 11);
        } elseif ('create' === $getter = substr($method, 0, 6)) {
            $entityName = substr($method, 6);
        } elseif ('find' === $getter = substr($method, 0, 4)) {
            $entityName = substr($method, 4);
        } elseif ('get' === $getter = substr($method, 0, 3)) {
            $entityName = substr($method, 3);
        } else {
            $getter     = null;
            $entityName = null;
        }

        if ($getter && array_key_exists($entityName, $this->getEntities())) {
            $method = $getter . 'Entity';

            return $this->$method($entityName, $args[0]);
        }

        throw new \BadMethodCallException(sprintf('There is no method named %s in FixturesContext', $method));
    }

    /**
     * @param string $entityName
     * @param mixed  $data
     *
     * @throws \InvalidArgumentException If entity is not found
     *
     * @return object
     */
    public function getEntity($entityName, $data)
    {
        $getter = sprintf('get%s', $entityName);

        if (method_exists($this, $getter)) {
            return $this->$getter($data);
        }

        return $this->getEntityOrException($entityName, $data);
    }

    /**
     * @param string $entityName
     * @param mixed  $data
     *
     * @return object
     */
    public function createEntity($entityName, $data)
    {
        $method = sprintf('create%s', $entityName);

        return $this->$method($data);
    }

    /**
     * @param string $entityName
     * @param string $data
     *
     * @return object
     */
    public function getOrCreateEntity($entityName, $data)
    {
        try {
            return $this->getEntity($entityName, $data);
        } catch (\InvalidArgumentException $e) {
            return $this->createEntity($entityName, $data);
        }
    }

    /**
     * @param string $entityName
     * @param mixed  $criteria
     *
     * @throws \Exception
     *
     * @return null|object
     */
    public function findEntity($entityName, $criteria)
    {
        if (!array_key_exists($entityName, $this->getEntities())) {
            throw new \Exception(sprintf('Unrecognized entity "%s".', $entityName));
        }

        if (gettype($criteria) === 'string' || $criteria === null) {
            $criteria = ['code' => $criteria];
        }

        try {
            return $this->spin(function () use ($entityName, $criteria) {
                return $this->getRepository($this->getEntities()[$entityName])->findOneBy($criteria);
            }, sprintf('Cannot find entity "%s"', $entityName));
        } catch (TimeoutException $exception) {
            return null;
        }
    }

    /**
     * @param string $entityName
     * @param mixed  $criteria
     *
     * @throws \InvalidArgumentException If entity is not found
     *
     * @return object
     */
    public function getEntityOrException($entityName, $criteria)
    {
        $entity = $this->findEntity($entityName, $criteria);

        if (!$entity) {
            if (is_string($criteria)) {
                $criteria = ['code' => $criteria];
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Could not find "%s" with criteria %s',
                    $this->getEntities()[$entityName],
                    print_r(Debug::export($criteria, 2), true)
                )
            );
        }

        return $entity;
    }

    protected function getAttributeBackendType(string $attributeCode): string
    {
        /** @var Connection $db */
        $db = $this->getMainContext()->getContainer()->get('doctrine.dbal.default_connection');

        $sql = "SELECT backend_type FROM pim_catalog_attribute WHERE code = :attribute_code";
        $stmt = $db->prepare($sql);
        $stmt->bindValue("attribute_code", $attributeCode);
        $stmt->execute();

        return $stmt->fetch()['backend_type'];
    }

    /**
     * @param mixed $value
     * @param ?ValueInterface $productValue
     * @param string $attributeCode
     * @param array $infos
     */
    protected function assertProductDataValueEquals($value, ?ValueInterface $productValue, string $attributeCode, $infos = [])
    {
        $backendType = $this->getAttributeBackendType($attributeCode);

        $priceCurrency = $infos['price_currency'] ?? null;

        if ('' === $value) {
            Assert::assertEmpty((string) $productValue, sprintf(
                'Expected value of attribute "%s" to be empty, "%s" found.',
                $attributeCode,
                (string) $productValue
            ));
        } elseif ('media' === $backendType) {
            // media filename is auto generated during media handling and cannot be guessed
            // (it contains a timestamp)
            if ('**empty**' === $value) {
                Assert::assertEmpty((string) $productValue, sprintf(
                    'Expected value of media attribute "%s" to be empty, "%s" found.',
                    $attributeCode,
                    (string) $productValue
                ));
            } else {
                Assert::assertTrue(
                    null !== $productValue->getData() &&
                    false !== strpos($productValue->getData()->getOriginalFilename(), $value)
                );
            }
        } elseif ('prices' === $backendType && null !== $priceCurrency) {
            // $priceCurrency can be null if we want to test all the currencies at the same time
            // in this case, it's a simple string comparison
            // example: 180.00 EUR, 220.00 USD

            $price = $productValue->getPrice($priceCurrency);

            if ($value === null) {
                Assert::isNull($price);
            } else {
                Assert::assertEquals((float)$value, (float)$price->getData());
            }
        } elseif ('boolean' === $backendType) {
            if ($value === "false") {
                $value = false;
            }
            if ($value === "true") {
                $value = true;
            }
            Assert::assertEquals((bool) $value, (bool) $productValue->getData());
        } elseif ('date' === $backendType) {
            Assert::assertEquals($value, $productValue->getData()->format('Y-m-d'));
        } elseif ('decimal' === $backendType) {
            Assert::assertEquals((float) $value, (float) $productValue->getData());
        } elseif ('option' === $backendType) {
            Assert::assertEquals($value, $productValue->getData());
        } elseif ('metric' === $backendType) {
            Assert::assertEquals((float) $value, (float) $productValue->getData()->getData());
        } elseif ('text' === $backendType) {
            Assert::assertEquals((string) $value, (string) $productValue->getData());
        } else {
            Assert::assertEquals($value, (string) $productValue);
        }
    }

    /**
     * @param mixed $object
     *
     * @throws \InvalidArgumentException
     */
    protected function validate($object)
    {
        if ($object instanceof ProductInterface) {
            $validator = $this->getContainer()->get('pim_catalog.validator.product');
        } else {
            $validator = $this->getContainer()->get('validator');
        }
        $violations = $validator->validate($object);

        if (0 < $violations->count()) {
            $messages = [];
            foreach ($violations as $violation) {
                $messages[] = $violation->getMessage();
            }

            throw new \InvalidArgumentException(
                sprintf(
                    'Object "%s" is not valid, cf following constraint violations "%s"',
                    ClassUtils::getClass($object),
                    implode(', ', $messages)
                )
            );
        }
    }

    /**
     * @param string $string
     *
     * @return string
     */
    protected function camelize($string)
    {
        return Inflector::camelize(str_replace(' ', '_', strtolower($string)));
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getMainContext()->getEntityManager();
    }

    /**
     * @param string $namespace
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($namespace)
    {
        return $this->getMainContext()->getEntityManager()->getRepository($namespace);
    }

    /**
     * @param object $object
     */
    public function refresh($object)
    {
        if (is_object($object)) {
            $this->getMainContext()->getEntityManager()->refresh($object);
        }
    }
}
