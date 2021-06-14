<?php

namespace Akeneo\Platform\Bundle\UIBundle;

use Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Compiler\RegisterFormExtensionsPass;
use Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Compiler\RegisterGenericProvidersPass;
use Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Compiler\RegisterViewElementsPass;
use Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\Routing\RequestContext;

/**
 * Override PimUIBundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimUIBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterFormExtensionsPass())
            ->addCompilerPass(new RegisterViewElementsPass(new ReferenceFactory()))
            ->addCompilerPass(new RegisterGenericProvidersPass(new ReferenceFactory(), 'field'))
            ->addCompilerPass(new RegisterGenericProvidersPass(new ReferenceFactory(), 'empty_value'))
            ->addCompilerPass(new RegisterGenericProvidersPass(new ReferenceFactory(), 'form'))
            ->addCompilerPass(new RegisterGenericProvidersPass(new ReferenceFactory(), 'filter'))
        ;
    }

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
        $url = getenv('AKENEO_PIM_URL');
        $scheme = parse_url($url, \PHP_URL_SCHEME);
        $host = parse_url($url, \PHP_URL_HOST);
        $port = parse_url($url, \PHP_URL_PORT);

        /** @var RequestContext $requestContext */
        $requestContext = $this->container->get('router')->getContext();
        $requestContext->setScheme($scheme);
        $requestContext->setHost($host);
        switch (strtolower($scheme)) {
            case 'https':
                $requestContext->setHttpsPort($port);
                break;
            case 'http':
                $requestContext->setHttpPort($port);
                break;
        }
    }
}
