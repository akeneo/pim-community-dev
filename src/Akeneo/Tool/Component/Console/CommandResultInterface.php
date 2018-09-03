<?php

/**
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Tool\Component\Console;

/**
 * Command result object
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 */
interface CommandResultInterface
{
    /**
     * @return array
     */
    public function getCommandOutput();

    /**
     * @return int
     */
    public function getCommandStatus();
}
