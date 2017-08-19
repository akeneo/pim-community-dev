<?php

declare(strict_types=1);

namespace Akeneo\Component\Batch\System;

/**
 * This class aims to get a system identifier.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface SystemIdProvider
{
    public function getSystemId() : string;
}
