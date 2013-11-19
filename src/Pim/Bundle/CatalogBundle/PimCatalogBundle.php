<?php

namespace Pim\Bundle\CatalogBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

/**
 * Pim Catalog Bundle
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container
            ->addCompilerPass(new Compiler\ResolveDoctrineOrmTargetEntitiesPass())
            ->addCompilerPass(new Compiler\RegisterAttributeConstraintGuessersPass())
            ->addCompilerPass(new Compiler\RegisterMassEditActionsPass());
    }
}
