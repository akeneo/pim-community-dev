<?php

namespace Pim\Bundle\ImportExportBundle;

use Pim\Bundle\EnrichBundle\DependencyInjection\Reference\ReferenceFactory;
use Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler\RegisterJobParametersFormsOptionsPass;
use Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler\RegisterJobTemplatePass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * The Pim Import Export Bundle
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimImportExportBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterJobTemplatePass())
            ->addCompilerPass(new RegisterJobParametersFormsOptionsPass(new ReferenceFactory()));
    }
}
