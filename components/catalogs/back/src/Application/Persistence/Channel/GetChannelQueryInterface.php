<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Channel;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetChannelQueryInterface
{
    /**
     * @return array{code: string, label: string}|null
     */
    public function execute(string $code): ?array;
}
