<?php

namespace Pim\Bundle\FlexibleEntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler\AddManagerCompilerPass;
use Pim\Bundle\FlexibleEntityBundle\DependencyInjection\Compiler\AddAttributeTypeCompilerPass;

/**
 * Flexible entity bundle
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
