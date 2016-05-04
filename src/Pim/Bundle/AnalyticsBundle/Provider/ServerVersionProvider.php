<?php

namespace Pim\Bundle\AnalyticsBundle\Provider;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Returns the version of the server (Apache/NGINX).
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ServerVersionProvider
{
    /** @var RequestStack */
    protected $requestStack;

    /**
     * @param RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * Provides server version.
     *
     * @return array
     */
    public function provide()
    {
        $version = '';
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $version = $request->server->get('SERVER_SOFTWARE');
        }

        return ['server_version' => $version];
    }
}
