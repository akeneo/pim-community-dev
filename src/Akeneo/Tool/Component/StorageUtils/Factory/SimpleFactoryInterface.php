<?php

namespace Akeneo\Tool\Component\StorageUtils\Factory;

/**
 * Simple object factory interface
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SimpleFactoryInterface
{
    /**
     * @return object
     */
    public function create();
}
