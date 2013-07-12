<?php
namespace Oro\Bundle\FlexibleEntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler\AddManagerCompilerPass;
use Oro\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler\AddAttributeTypeCompilerPass;

/**
 * Flexible entity bundle
 *
 *
 */
class OroFlexibleEntityBundle extends Bundle
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
