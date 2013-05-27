<?php
namespace Oro\Bundle\FlexibleEntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler\AddManagerCompilerPass;
use Oro\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler\AddAttributeTypeCompilerPass;

/**
 * Flexible entity bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
