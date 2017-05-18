<?php

namespace Pim\Bundle\ApiBundle;

use Pim\Bundle\ApiBundle\DependencyInjection\Compiler\ContentTypeNegotiatorPass;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PimApiBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterSerializerPass('pim_external_api_exception_serializer'))
            ->addCompilerPass(new ContentTypeNegotiatorPass())
        ;
    }
}
