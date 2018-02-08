<?php

namespace Context;

use Akeneo\Component\Console\CommandLauncher;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

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

        $application = new Application($this->getContainer()->get('kernel'));
        $application->setAutoExit(false);

        $commands = [
            'pimee:installer:clean-category-accesses',
            'pimee:installer:clean-attribute-group-accesses'
        ];

        foreach ($commands as $command) {
            $input = new ArrayInput([
                'command'  => $command,
            ]);
            $output = new BufferedOutput();
            $exitCode = $application->run($input, $output);

            if (0 !== $exitCode) {
                throw new \Exception(sprintf('Command "%s" failed when loading catalog: "%s"', $command, $output->fetch()));
            }
        }
    }
}
