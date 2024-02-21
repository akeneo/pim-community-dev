<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\FrameworkBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\RequestContext;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimFrameworkBundle extends Bundle
{
    /**
     * {@inheritdoc }
     */
    public function boot()
    {
        parent::boot();

        $this->setupRequestContext();
    }

    private function setupRequestContext(): void
    {
        $url = $this->container->get('pim_framework.service.pim_url')->getPimUrl();

        $scheme = parse_url($url, \PHP_URL_SCHEME);
        $host = parse_url($url, \PHP_URL_HOST);
        $port = (int) parse_url($url, \PHP_URL_PORT);

        /** @var RequestContext $requestContext */
        $requestContext = $this->container->get('router')->getContext();
        $requestContext->setHost($host);

        if ($scheme !== null) {
            $requestContext->setScheme($scheme);

            switch (strtolower($scheme)) {
                case 'https':
                    $requestContext->setHttpsPort($port ?: 443);
                    break;
                case 'http':
                    $requestContext->setHttpPort($port ?: 80);
                    break;
            }
        }
    }
}
