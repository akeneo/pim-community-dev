<?php

declare(strict_types=1);

namespace Akeneo\Channel\Component\Query;

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetChannelCategoryCodeInterface
{
    public function execute(string $channelCode): ?string;
}
