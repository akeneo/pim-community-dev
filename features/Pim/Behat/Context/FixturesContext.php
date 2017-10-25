<?php

namespace Pim\Behat\Context;

use Context\Spin\SpinCapableTrait;
use Context\Spin\TimeoutException;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Util\Debug;
use Doctrine\Common\Util\Inflector;
use Pim\Component\Catalog\Model\FamilyVariant;
use Pim\Component\Catalog\Model\ProductInterface;

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
        'Attribute'        => 'PimCatalogBundle:Attribute',
        'AttributeGroup'   => 'PimCatalogBundle:AttributeGroup',
        'AttributeOption'  => 'PimCatalogBundle:AttributeOption',
        'Channel'          => 'PimCatalogBundle:Channel',
        'Currency'         => 'PimCatalogBundle:Currency',
        'Family'           => 'PimCatalogBundle:Family',
        'FamilyVariant'    => FamilyVariant::class,
        'Category'         => 'PimCatalogBundle:Category', // TODO: To remove
        'ProductCategory'  => 'PimCatalogBundle:Category',
        'AssociationType'  => 'PimCatalogBundle:AssociationType',
        'JobInstance'      => 'Akeneo\Component\Batch\Model\JobInstance',
        'JobConfiguration' => 'Pim\Component\Connector\Model\JobConfiguration',
        'User'             => 'PimUserBundle:User',
        'Role'             => 'OroUserBundle:Role',
        'UserGroup'        => 'OroUserBundle:Group',
        'Locale'           => 'PimCatalogBundle:Locale',
        'GroupType'        => 'PimCatalogBundle:GroupType',
        'Product'          => 'Pim\Component\Catalog\Model\Product',
        'ProductGroup'     => 'Pim\Bundle\CatalogBundle\Entity\Group',
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

    /**
     * @param mixed  $data
     * @param string $value
     */
    protected function assertDataEquals($data, $value)
    {
        switch ($value) {
            case 'true':
                assertTrue($data);
                break;

            case 'false':
                assertFalse($data);
                break;

            default:
                if ($data instanceof \DateTime) {
                    $data = $data->format('Y-m-d');
                }
                assertEquals($value, $data);
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
