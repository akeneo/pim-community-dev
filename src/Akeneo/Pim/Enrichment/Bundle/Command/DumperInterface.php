<?php

namespace Akeneo\Pim\Enrichment\Bundle\Command;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Dump data in console output
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DumperInterface
{
    /**
     * Dump data
     *
     * @param OutputInterface $output
     */
    public function dump(OutputInterface $output);
}
