<?php

namespace Pim\Behat\Decorator\Common\AddSelect;

/**
 * Add select decorator interface
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface AddSelectInterface
{
    /**
     * Check and add list of given options
     *
     * @param array $options
     */
    public function addOptions(array $options);

    /**
     * Checks if option exists
     *
     * @param string $option
     *
     * @return boolean
     */
    public function hasOption($option);
}
