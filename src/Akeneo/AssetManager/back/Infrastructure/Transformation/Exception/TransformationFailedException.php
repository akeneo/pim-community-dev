<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Transformation\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class TransformationFailedException extends \RuntimeException
{
    public static function createFromViolationList(ConstraintViolationListInterface $violations): self
    {
        $errorMessages = [];
        foreach ($violations as $violation) {
            $errorMessages[] = $violation->getMessage();
        }

        return new self(implode(', ', $errorMessages));
    }
}
