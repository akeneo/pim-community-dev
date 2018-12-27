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

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Client\Franklin\Exception;

/**
 * Exception thrown when the user does not have enough credits to subscribe a product to Franklin.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class InsufficientCreditsException extends ClientException
{
    public function __construct()
    {
        parent::__construct('Not enough credits on Franklin to subscribe.');
    }
}
