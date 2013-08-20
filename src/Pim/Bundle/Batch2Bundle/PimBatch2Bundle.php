<?php

namespace Pim\Bundle\Batch2Bundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\Batch2Bundle\DependencyInjection\Compiler\InjectEventDispatcherPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimBatch2Bundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new InjectEventDispatcherPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
