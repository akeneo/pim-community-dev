<?php

namespace Context;

use Akeneo\Component\Console\CommandLauncher;

/**
 * A context for initializing catalog configuration
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class EnterpriseCatalogConfigurationContext extends CatalogConfigurationContext
{
    /**
     *{@inheritdoc}
     */
    public function aCatalogConfiguration($catalog)
    {
        parent::aCatalogConfiguration($catalog);

        $launcher = new CommandLauncher(
            $this->getContainer()->getParameter('kernel.root_dir'),
            $this->getContainer()->getParameter('kernel.environment')
        );
        $launcher->executeForeground('pimee:installer:clean-category-accesses');
    }
}
