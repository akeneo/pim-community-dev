<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\Product\Api\Command\Exception;

use Symfony\Component\Validator\ConstraintViolationList;

final class LegacyViolationsException extends \LogicException
{
    public function __construct(private ConstraintViolationList $constraintViolationList)
    {
        parent::__construct((string) $this->constraintViolationList);
    }

    public function violations(): ConstraintViolationList
    {
        return $this->constraintViolationList;
    }
}
