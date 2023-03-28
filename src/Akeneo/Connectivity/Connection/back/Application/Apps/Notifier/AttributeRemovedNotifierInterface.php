<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Apps\Notifier;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeRemovedNotifierInterface
{
    public function notify(Catalog $catalog): void;
}
