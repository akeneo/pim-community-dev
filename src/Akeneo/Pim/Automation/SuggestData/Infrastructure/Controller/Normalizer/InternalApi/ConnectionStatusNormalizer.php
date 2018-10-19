<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Controller\Normalizer\InternalApi;

use Akeneo\Pim\Automation\SuggestData\Domain\Model\Read\ConnectionStatus;

/**
 * Normalizes a ConnectionStatus read model for the front-end.
 *
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
final class ConnectionStatusNormalizer
{
    /**
     * @param ConnectionStatus $connectionStatus
     *
     * @return array
     */
    public function normalize(ConnectionStatus $connectionStatus): array
    {
        return [
            'isActive' => $connectionStatus->isActive(),
        ];
    }
}
