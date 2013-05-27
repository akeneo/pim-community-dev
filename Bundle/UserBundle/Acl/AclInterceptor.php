<?php
namespace Oro\Bundle\UserBundle\Acl;

use CG\Proxy\MethodInterceptorInterface;
use CG\Proxy\MethodInvocation;
use Symfony\Component\HttpKernel\Log\LoggerInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Oro\Bundle\UserBundle\Acl\Manager;
use Oro\Bundle\UserBundle\Entity\Acl;

class AclInterceptor implements MethodInterceptorInterface
{
    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    private $securityContext;

    /**
     * @var \Symfony\Component\HttpKernel\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $reader;

    /**
     * @var \Symfony\Component\Security\Core\Authorization\AccessDecisionManager
     */
    private $accessDecisionManager;

    public function __construct(ContainerInterface $container)
    {
        $this->securityContext = $container->get('security.context');
        $this->logger = $container->get('logger');
        $this->container = $container;
        $this->reader = $container->get('annotation_reader');
        $this->accessDecisionManager = $container->get('security.access.decision_manager');
    }

    public function intercept(MethodInvocation $method)
    {
        $this->logger->info(
            sprintf('User invoked class: "%s", Method: "%s".', $method->reflection->class, $method->reflection->name)
        );

        $token = $this->securityContext->getToken();
        if ($token) {
            $aclId = $this->getAclId($method);
            if (!$aclId) {
                $accessRoles = $this->getAclManager()->getAclRolesWithoutTree(Acl::ROOT_NODE);
            } else {
                $accessRoles = $this->getAclManager()->getAclRoles($aclId);
            }

            if (false === $this->accessDecisionManager->decide($token, $accessRoles, $method)) {
                //check if we have internal action - show blank
                if ($this->container->get('request')->attributes->get('_route') == '_internal') {
                    return new Response('');
                }

                throw new AccessDeniedException('Access denied.');
            }
        }

        return $method->proceed();
    }

    /**
     * Try to get ACL id for method
     *
     * @param  \CG\Proxy\MethodInvocation $method
     * @return string|bool
     */
    private function getAclId(MethodInvocation $method)
    {
        $reflection = $method->reflection;
        //get acl from annotation
        $aclResource = $this->reader->getMethodAnnotation(
            $reflection,
            Manager::ACL_ANNOTATION_CLASS
        );
        // get acl from acl ancestor annotation
        if (!$aclResource) {
            $aclResource = $this->reader->getMethodAnnotation(
                $reflection,
                Manager::ACL_ANCESTOR_ANNOTATION_CLASS
            );
        }
        // get acl from config
        if (!$aclResource) {
            $aclId = $this->container->get('oro_user.acl_config_reader')->getMethodAclId(
                $reflection->class,
                $reflection->name
            );
        } else {
            //get acl id
            $aclId = $aclResource->getId();
        }

        return $aclId;
    }

    /**
     * @return \Oro\Bundle\UserBundle\Acl\Manager
     */
    public function getAclManager()
    {
        return $this->container->get('oro_user.acl_manager');
    }
}
