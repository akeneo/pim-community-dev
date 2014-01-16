<?php

namespace Pim\Bundle\InstallerBundle\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Oro\Bundle\InstallerBundle\Command\InstallCommand as OroInstallCommand;

/**
 * Override OroInstaller command to add PIM custom rules
 *
 * @author    Nicolas <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InstallCommand extends OroInstallCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        parent::configure();

        $this
            ->setName('pim:install')
            ->setDescription('Akeneo PIM Application Installer.');
    }

    /**
     * Override to add custom commands
     *
     * {@inheritDoc}
     */
    protected function setupStep(InputInterface $input, OutputInterface $output)
    {
        parent::setupStep($input, $output);

        $this
            ->runCommand('pim:search:reindex', $input, $output, array('locale' => 'en_US'))
            ->runCommand('pim:versioning:refresh', $input, $output)
            ->runCommand('doctrine:query:sql', $input, $output, array('sql' => '"ANALYZE TABLE pim_product_value"'))
            ->runCommand('doctrine:query:sql', $input, $output, array('sql' => '"ANALYZE TABLE pim_icecatdemo_product_value"'))
            ->runCommand('pim:completeness:calculate', $input, $output);

        return $this;
    }
}
