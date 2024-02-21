<?php

namespace Akeneo\Tool\Bundle\ApiBundle;

use Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\RegisterSerializerPass;
use Akeneo\Tool\Bundle\ApiBundle\DependencyInjection\Compiler\ContentTypeNegotiatorPass;
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
