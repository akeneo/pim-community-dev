<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * A factory class to create ACL ObjectIdentity objects
 */
class ObjectIdentityFactory
{
    /**
     * A map which is used to auto detect correct creation method depend on object identity type
     * This map is used in get method
     * @see get
     *
     * @var array
     */
    protected static $methodMap = array(
        'class' => 'forClass',
        'entity' => 'forEntityClass',
        'action' => 'forAction',
    );

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var ObjectIdentity
     */
    protected $root;

    /**
     * An associative array of entity class names
     * The key of this array if an entity name, for example: AcmeBundle:SomeEntity
     * The value is the full class name, for example: AcmeBundle\Entity\SomeEntity
     *
     * @var array
     */
    protected $entityClassNames;

    /**
     * Constructor
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->em = $em;
        $this->root = new ObjectIdentity('root', 'Root');
    }

    /**
     * Constructs an ObjectIdentity is used for grant default permissions
     * if more appropriate permissions are not specified
     *
     * @return ObjectIdentity
     */
    public function root()
    {
        return $this->root;
    }

    /**
     * Constructs an ObjectIdentity for the given domain object
     *
     * @param object $obj
     * @return ObjectIdentity
     */
    public function fromDomainObject($obj)
    {
        return ObjectIdentity::fromDomainObject($obj);
    }

    /**
     * Constructs an ObjectIdentity for the given domain object
     *
     * @param object $obj
     * @return ObjectIdentity
     */
    public function fromEntityObject($obj)
    {
        return $this->fromDomainObject($obj);
    }

    /**
     * Constructs an ObjectIdentity based on the given descriptor
     * Examples:
     *     create('Class:AcmeBundle\SomeClass')
     *     create('Entity:AcmeBundle:SomeEntity')
     *     create('Action:Some Action')
     *
     * @param string $descriptor The object identity descriptor
     * @return ObjectIdentity
     * @throws \InvalidArgumentException
     */
    public function get($descriptor)
    {
        $delim = strpos($descriptor, ':');
        if (!$delim) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Incorrect descriptor: %s. Expected IdentifierType:Name.',
                    $descriptor
                )
            );
        }

        $type = strtolower(substr($descriptor, 0, $delim));
        if (!isset(static::$methodMap[$type])) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unknown object identifier type: %s. Descriptor: %s.',
                    substr($descriptor, 0, $delim),
                    $descriptor
                )
            );
        }

        $method = static::$methodMap[$type];

        return $this->{$method}(substr($descriptor, $delim + 1));
    }

    /**
     * Constructs an ObjectIdentity for the given class
     *
     * @param string $className
     * @return ObjectIdentity
     */
    public function forClass($className)
    {
        return new ObjectIdentity('class', $className);
    }

    /**
     * Constructs an ObjectIdentity for the given entity type
     *
     * @param string $entityName The name of the entity.
     * @return ObjectIdentity
     */
    public function forEntityClass($entityName)
    {
        if (!isset($this->entityClassNames)) {
            $this->entityClassNames = array();
        }
        if (isset($this->entityClassNames[$entityName])) {
            $entityClass = $this->entityClassNames[$entityName];
        } else {
            $entityClass = $this->getEntityClass($entityName);
            $this->entityClassNames[$entityName] = $entityClass;
        }

        return $this->forClass($entityClass);
    }

    /**
     * Gets the full class name for the given entity
     *
     * @param string $entityName
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function getEntityClass($entityName)
    {
        $split = explode(':', $entityName);
        if (count($split) <= 1) {
            // The given entity name is not in bundle:entity format. Suppose that it is the full class name
            return $entityName;
        } elseif (count($split) > 2) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Incorrect entity name: %s. Expected the full class name or bundle:entity.',
                    $entityName
                )
            );
        }

        return $this->em->getConfiguration()->getEntityNamespace($split[0]) . '\\' . $split[1];
    }

    /**
     * Constructs an ObjectIdentity for the given action
     *
     * @param string $actionName
     * @return ObjectIdentity
     */
    public function forAction($actionName)
    {
        return new ObjectIdentity('action', $actionName);
    }
}
