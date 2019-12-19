<?php

declare(strict_types=1);

namespace Akeneo\Apps\Domain\Audit\Persistence\Query;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface ExtractAppsEventCountQuery
{
    public function extractCreatedProducts(\DateTime $dateTime): array;

    public function extractUpdatedProducts(\DateTime $dateTime): array;
}
