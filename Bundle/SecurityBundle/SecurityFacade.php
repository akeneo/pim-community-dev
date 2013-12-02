<?php

namespace Oro\Bundle\SecurityBundle;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;

class SecurityFacade
{
    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var AclAnnotationProvider
     */
    protected $annotationProvider;

    /**
     * @var ObjectIdentityFactory
     */
    protected $objectIdentityFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param SecurityContextInterface $securityContext
     * @param AclAnnotationProvider    $annotationProvider
     * @param ObjectIdentityFactory    $objectIdentityFactory
     * @param LoggerInterface          $logger
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        AclAnnotationProvider $annotationProvider,
        ObjectIdentityFactory $objectIdentityFactory,
        LoggerInterface $logger
    ) {
        $this->securityContext = $securityContext;
        $this->annotationProvider = $annotationProvider;
        $this->objectIdentityFactory = $objectIdentityFactory;
        $this->logger = $logger;
    }

    /**
     * Checks if an access to the given method of the given class is granted to the caller
     *
     * @param  string $class
     * @param  string $method
     * @return bool
     */
    public function isClassMethodGranted($class, $method)
    {
        $isGranted = true;

        // check method level ACL
        $annotation = $this->annotationProvider->findAnnotation($class, $method);
        if ($annotation !== null) {
            $this->logger->debug(
                sprintf('Check an access using "%s" ACL annotation.', $annotation->getId())
            );
            $isGranted = $this->securityContext->isGranted(
                $annotation->getPermission(),
                $this->objectIdentityFactory->get($annotation)
            );
        }

        // check class level ACL
        if ($isGranted && ($annotation === null || !$annotation->getIgnoreClassAcl())) {
            $annotation = $this->annotationProvider->findAnnotation($class);
            if ($annotation !== null) {
                $this->logger->debug(
                    sprintf('Check an access using "%s" ACL annotation.', $annotation->getId())
                );
                $isGranted = $this->securityContext->isGranted(
                    $annotation->getPermission(),
                    $this->objectIdentityFactory->get($annotation)
                );
            }
        }

        return $isGranted;
    }

    /**
     * Get permission for given class and method from the ACL annotation
     *
     * @param $class
     * @param $method
     * @return string
     */
    public function getClassMethodAnnotationPermission($class, $method)
    {
        $annotation = $this->annotationProvider->findAnnotation($class, $method);

        if ($annotation) {
            return $annotation->getPermission();
        }
    }

    /**
     * Checks if an access to a resource is granted to the caller
     *
     * @param string|string[] $attributes Can be a role name(s), permission name(s), an ACL annotation id
     *                                    or something else, it depends on registered security voters
     * @param  mixed $object A domain object, object identity or object identity descriptor (id:type)
     * @return bool
     */
    public function isGranted($attributes, $object = null)
    {
        if ($object === null
            && is_string($attributes)
            && $annotation = $this->annotationProvider->findAnnotationById($attributes)
        ) {
            $this->logger->debug(sprintf('Check an access using "%s" ACL annotation.', $annotation->getId()));
            $isGranted = $this->securityContext->isGranted(
                $annotation->getPermission(),
                $this->objectIdentityFactory->get($annotation)
            );
        } elseif (is_string($object)) {
            $isGranted = $this->securityContext->isGranted(
                $attributes,
                $this->objectIdentityFactory->get($object)
            );
        } else {
            $isGranted = $this->securityContext->isGranted($attributes, $object);
        }

        return $isGranted;
    }
}
