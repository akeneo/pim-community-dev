<?php

namespace Akeneo\Component\Batch\Item;

/**
 * StepElementInterface
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface StepElementInterface
{
    /**
     * Return name
     *
     * @return string
     */
    public function getName();
}