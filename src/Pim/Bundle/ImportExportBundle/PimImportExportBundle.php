<?php

namespace Pim\Bundle\ImportExportBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\BatchBundle\Connector\Connector;

/**
 * The Pim Import Export Bundle
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimImportExportBundle extends Connector
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new DependencyInjection\Compiler\ResolveDoctrineOrmTargetEntitiesPass())
            ->addCompilerPass(new DependencyInjection\Compiler\ReplacePimSerializerArgumentsPass())
            ->addCompilerPass(new DependencyInjection\Compiler\RegisterArchiversPass())
            ->addCompilerPass(new DependencyInjection\Compiler\TransformerGuesserPass());
    }
}
