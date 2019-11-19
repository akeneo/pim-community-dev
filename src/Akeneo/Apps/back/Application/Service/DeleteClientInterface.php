<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Service;

use Akeneo\Apps\Domain\Model\ValueObject\ClientId;

/**
 * @author    Pierre Jolly <pierre.jolly@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface DeleteClientInterface
{
    public function execute(ClientId $clientId): void;
}
