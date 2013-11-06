<?php
namespace Pim\Bundle\FlexibleEntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler\AddManagerCompilerPass;
use Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler\AddAttributeTypeCompilerPass;

/**
 * Flexible entity bundle
 *
 *
 */
class PimFlexibleEntityBundle extends Bundle
{

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddManagerCompilerPass());
        $container->addCompilerPass(new AddAttributeTypeCompilerPass());
    }
}
