<?php

namespace Pim\Behat\Context;

use Behat\Behat\Context\Step;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\Common\Util\Inflector;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Component\Connector\Processor\Denormalization\ProductProcessor;

/**
 * A context for creating entities
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FixturesContext extends PimContext
{
    protected $entities = [
        'Attribute'        => 'PimCatalogBundle:Attribute',
        'AttributeGroup'   => 'PimCatalogBundle:AttributeGroup',
        'AttributeOption'  => 'PimCatalogBundle:AttributeOption',
        'Channel'          => 'PimCatalogBundle:Channel',
        'Currency'         => 'PimCatalogBundle:Currency',
        'Family'           => 'PimCatalogBundle:Family',
        'Category'         => 'PimCatalogBundle:Category', // TODO: To remove
        'ProductCategory'  => 'PimCatalogBundle:Category',
        'AssociationType'  => 'PimCatalogBundle:AssociationType',
        'JobInstance'      => 'AkeneoBatchBundle:JobInstance',
        'JobConfiguration' => 'Pim\Component\Connector\Model\JobConfiguration',
        'User'             => 'PimUserBundle:User',
        'Role'             => 'OroUserBundle:Role',
        'UserGroup'        => 'OroUserBundle:Group',
        'Locale'           => 'PimCatalogBundle:Locale',
        'GroupType'        => 'PimCatalogBundle:GroupType',
        'Product'          => 'Pim\Bundle\CatalogBundle\Model\Product',
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

        return $this->getRepository($this->getEntities()[$entityName])->findOneBy($criteria);
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
                    print_r(\Doctrine\Common\Util\Debug::export($criteria, 2), true)
                )
            );
        }

        return $entity;
    }

    /**
     * @param string $entityName
     *
     * @Given /^there is no (.*)$/
     *
     * @throws \Exception
     */
    public function thereIsNoEntity($entityName)
    {
        if (strpos($entityName, ' ')) {
            $entityName = implode('', array_map('ucfirst', explode(' ', $entityName)));
        }

        $entityName = ucfirst($entityName);

        if (!array_key_exists($entityName, $this->getEntities())) {
            throw new \Exception(sprintf('Unrecognized entity "%s".', $entityName));
        }

        $namespace = $this->getEntities()[$entityName];
        $entities  = $this->getRepository($namespace)->findAll();

        foreach ($entities as $entity) {
            // TODO use a Remover
            $this->remove($entity, false);
        }
        $this->flush();
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
     * Load an installer fixture
     *
     * @param string $type
     * @param array  $data
     * @param string $format
     *
     * @return object
     */
    public function loadFixture($type, array $data, $format = 'csv')
    {
        $processor = $this
            ->getContainer()
            ->get('pim_installer.fixture_loader.configuration_registry')
            ->getProcessor($type, $format);

        if ($processor instanceof ProductProcessor) {
            $processor->setEnabledComparison(false);
        }

        $entity = $processor->process($data);

        // we encountered a bunch of invalid data in behat due to old way to import them
        // could be removed once all the fixtures will use the new API (processor, updater, validator)
        $this->validate($entity);

        return $entity;
    }

    /**
     * @param mixed $object
     *
     * @throws \InvalidArgumentException
     */
    protected function validate($object)
    {
        // TODO: split UniqueVariantAxis + spec
        // TODO: rework validation constraint to forbid to add products with same options in variant group in same time
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
     * @return \Doctrine\Common\Persistence\ManagerRegistry
     */
    protected function getSmartRegistry()
    {
        return $this->getMainContext()->getSmartRegistry();
    }

    /**
     * @param string $namespace
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getRepository($namespace)
    {
        return $this->getSmartRegistry()->getManagerForClass($namespace)->getRepository($namespace);
    }

    /**
     * @param object $object
     */
    protected function refresh($object)
    {
        if (is_object($object)) {
            $this->getSmartRegistry()->getManagerForClass(get_class($object))->refresh($object);
        }
    }

    /**
     * @param object $object
     * @param bool   $flush
     *
     * TODO use Savers
     */
    protected function persist($object, $flush = true)
    {
        $manager = $this->getSmartRegistry()->getManagerForClass(get_class($object));
        $manager->persist($object);

        if ($flush) {
            $manager->flush($object);
        }
    }

    /**
     * @param object $object
     * @param bool   $flush
     *
     * * TODO use Removers
     */
    protected function remove($object, $flush = true)
    {
        $manager = $this->getSmartRegistry()->getManagerForClass(get_class($object));
        $manager->remove($object);

        if ($flush) {
            $manager->flush($object);
        }
    }

    /**
     * @param object $object
     */
    public function flush($object = null)
    {
        if (!$object) {
            $this->flushAll();

            return;
        }

        $manager = $this->getSmartRegistry()->getManagerForClass(get_class($object));
        $manager->flush($object);
    }

    /**
     * Flush all managers
     */
    protected function flushAll()
    {
        foreach ($this->getSmartRegistry()->getManagers() as $manager) {
            $manager->flush();
        }
    }
}
