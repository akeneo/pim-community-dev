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

class InvitationException extends \Exception
{
    private string $errorCode;

    public function __construct(string $errorCode)
    {
        $this->errorCode = $errorCode;

        parent::__construct();
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }
}
