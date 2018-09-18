<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\Query;

use Akeneo\Platform\Component\CatalogVolumeMonitoring\Volume\ReadModel\CountVolume;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface CountQuery
{
    public function fetch(): CountVolume;
}
