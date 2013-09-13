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
     * @param AclAnnotationProvider $annotationProvider
     * @param ObjectIdentityFactory $objectIdentityFactory
     * @param LoggerInterface $logger
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
     * Checks if an access to the given method of the given class is granted
     *
     * @param string $class
     * @param string $method
     * @return bool
     */
    public function isClassMethodGranted($class, $method)
    {
        $isGranted = true;

        // check method level ACL
        $annotation = $this->annotationProvider->findAnnotation($class, $method);
        if ($annotation !== null) {
            $this->logger->info(
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
                $this->logger->info(
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
     * Checks if an access to a resource is granted
     *
     * Checks if an access to a resource protected the given ACL annotation is granted
     *
     * @param string $aclId
     * @return bool
     */
    public function isGranted($aclId)
    {
        $isGranted = true;

        $annotation = $this->annotationProvider->findAnnotationById($aclId);
        if ($annotation !== null) {
            $this->logger->info(
                sprintf('Check an access using "%s" ACL annotation.', $annotation->getId())
            );
            $isGranted = $this->securityContext->isGranted(
                $annotation->getPermission(),
                $this->objectIdentityFactory->get($annotation)
            );
        }

        return $isGranted;
    }
}
