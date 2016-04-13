<?php

namespace Akeneo\Component\Batch\Item;

/**
 * InitializableInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface InitializableInterface
{
    /**
     * Custom logic on step initialization.
     */
    public function initialize();
}
