<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\Query;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\HelperSet;

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
     * @param HelperSet       $helperSet
     */
    public function dump(OutputInterface $output, HelperSet $helperSet);
}
