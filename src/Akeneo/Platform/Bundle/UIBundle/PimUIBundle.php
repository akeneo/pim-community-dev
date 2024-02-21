<?php

namespace Akeneo\Platform\Bundle\UIBundle;

use Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Compiler\RegisterFormExtensionsPass;
use Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Compiler\RegisterGenericProvidersPass;
use Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Compiler\RegisterViewElementsPass;
use Akeneo\Platform\Bundle\UIBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

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
}
