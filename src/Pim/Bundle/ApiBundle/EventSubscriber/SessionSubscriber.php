<?php

namespace Pim\Bundle\ApiBundle\EventSubscriber;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;
use Symfony\Component\Security\Http\FirewallMapInterface;
use Symfony\Bundle\SecurityBundle\Security\FirewallMap;

/**
 * Sets the session in the request only if the security firewall is not stateless.
 * The default SessionListener doesn't care about the authentication and only use the 'session' setting of the framework.
 * In the same way, the 'stateless' setting of the firewall only affect the use of the session for authentication purpose.
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SessionSubscriber extends AbstractSessionListener
{
    private $session;
    private $requestStack;
    private $firewallMap;

    public function __construct(SessionInterface $session = null, RequestStack $requestStack, FirewallMapInterface $firewallMap)
    {
        $this->session = $session;
        $this->requestStack = $requestStack;
        $this->firewallMap = $firewallMap;
    }

    protected function getSession(): ?SessionInterface
    {
        if ($this->firewallMap instanceof FirewallMap) {
            $firewallConfig = $this->firewallMap->getFirewallConfig($this->requestStack->getCurrentRequest());
            if (null !== $firewallConfig && $firewallConfig->isStateless() === true) {
                return null;
            }
        }

        return $this->session;
    }
}
