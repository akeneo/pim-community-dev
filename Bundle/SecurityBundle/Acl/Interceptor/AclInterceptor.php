<?php

namespace Oro\Bundle\SecurityBundle\Acl\Interceptor;

use CG\Proxy\MethodInterceptorInterface;
use CG\Proxy\MethodInvocation;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\SecurityBundle\Acl\Domain\ObjectIdentityFactory;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;

class AclInterceptor implements MethodInterceptorInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var AclAnnotationProvider
     */
    protected $annotationProvider;

    /**
     * @var ObjectIdentityFactory
     */
    protected $objectIdentityFactory;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param SecurityContextInterface $securityContext
     * @param AclAnnotationProvider $annotationProvider
     * @param Request|null $request
     */
    public function __construct(
        LoggerInterface $logger,
        SecurityContextInterface $securityContext,
        AclAnnotationProvider $annotationProvider,
        Request $request = null
    ) {
        $this->logger = $logger;
        $this->securityContext = $securityContext;
        $this->annotationProvider = $annotationProvider;
    }

    /**
     * Check whether an access is granted for the given method.
     *
     * @param MethodInvocation $method
     * @return mixed|Response
     * @throws AccessDeniedException
     */
    public function intercept(MethodInvocation $method)
    {
        $this->logger->info(
            sprintf('User invoked class: "%s", Method: "%s".', $method->reflection->class, $method->reflection->name)
        );

        $annotation = $this->annotationProvider->findAnnotation($method->reflection->class, $method->reflection->name);
        if ($annotation === null) {
            $annotation = $this->annotationProvider->findAnnotation($method->reflection->class);
        }

        $isGranted = true;
        if ($annotation !== null) {
            $this->logger->info(
                sprintf('Check access based on "%s" ACL annotation.', $annotation->getId())
            );
            $isGranted = $this->securityContext->isGranted(
                $annotation->getPermission(),
                $this->objectIdentityFactory->get($annotation)
            );
        }

        if (!$isGranted) {
            // check if we have internal action - show blank
            if ($this->request !== null && $this->request->attributes->get('_route') == null) {
                return new Response('');
            }
            throw new AccessDeniedException('Access denied.');
        }

        return $method->proceed();
    }
}
