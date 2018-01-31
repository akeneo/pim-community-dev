<?php

namespace Pim\Bundle\EnrichBundle;

use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterProductQueryFilterPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;
use Pim\Bundle\EnrichBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Enrich bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimEnrichBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\RegisterViewElementsPass(new ReferenceFactory()))
            ->addCompilerPass(new Compiler\RegisterFormExtensionsPass())
            ->addCompilerPass(new Compiler\RegisterGenericProvidersPass(new ReferenceFactory(), 'field'))
            ->addCompilerPass(new Compiler\RegisterGenericProvidersPass(new ReferenceFactory(), 'empty_value'))
            ->addCompilerPass(new Compiler\RegisterGenericProvidersPass(new ReferenceFactory(), 'form'))
            ->addCompilerPass(new Compiler\RegisterGenericProvidersPass(new ReferenceFactory(), 'filter'))
            ->addCompilerPass(new Compiler\RegisterCategoryItemCounterPass())
            ->addCompilerPass(new RegisterSerializerPass('pim_internal_api_serializer'))
            ->addCompilerPass(new RegisterProductQueryFilterPass('product_and_product_model'))
        ;
    }
}
