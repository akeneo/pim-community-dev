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

namespace Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\PimAi\Exception;

use Akeneo\Pim\Automation\SuggestData\Infrastructure\Client\Exception\ClientException;

/**
 * Exception thrown when a request to Franklin fails because of invalid request parameters.
 *
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class BadRequestException extends ClientException
{
}
