<?php

declare(strict_types=1);

namespace Akeneo\Tool\Component\Batch\Step;

/**
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
interface StoppableStepInterface
{
    public function setStoppable(bool $stoppable): void;
}
