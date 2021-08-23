<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Domain\Exception;

final class InvalidEmailException extends InvitationException
{
    public const ERROR_CODE = 'invalid_email';

    public function __construct()
    {
        parent::__construct(self::ERROR_CODE);
    }
}
