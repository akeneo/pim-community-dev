<?php

namespace Oro\Bundle\SecurityBundle\Acl\Interceptor;

use CG\Proxy\MethodInterceptorInterface;
use CG\Proxy\MethodInvocation;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Oro\Bundle\SecurityBundle\SecurityFacade;

class AclInterceptor implements MethodInterceptorInterface
{
    /**
     * @var SecurityFacade
     */
    private $securityFacade;

    /**
     * @var Request
     */
    private $request;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor
     *
     * @param SecurityFacade $securityFacade
     * @param Request $request
     * @param LoggerInterface $logger
     */
    public function __construct(
        SecurityFacade $securityFacade,
        Request $request,
        LoggerInterface $logger
    ) {
        $this->securityFacade = $securityFacade;
        $this->request = $request;
        $this->logger = $logger;
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

        if (!$this->securityFacade->isClassMethodGranted($method->reflection->class, $method->reflection->name)) {
            // check if we have internal action - show blank
            if ($this->request !== null && $this->request->attributes->get('_route') == null) {
                return new Response('');
            }
            throw new AccessDeniedException('Access denied.');
        }

        return $method->proceed();
    }
}
