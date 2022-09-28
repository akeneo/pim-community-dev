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

namespace Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\OperationApplier;

use Akeneo\Platform\TailoredImport\Application\ExecuteDataMapping\Exception\UnexpectedValueException;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\InvalidValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\StringValue;
use Akeneo\Platform\TailoredImport\Domain\Model\Value\ValueInterface;

final class CleanHTMLOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, ValueInterface $value): ValueInterface
    {
        return match (true) {
            !$operation instanceof CleanHTMLOperation => throw new UnexpectedValueException($operation, CleanHTMLOperation::class, self::class),
            $value instanceof InvalidValue => $value,
            $value instanceof StringValue => $this->findModeAndApply($operation, $value),
            default => throw new UnexpectedValueException($value, StringValue::class, self::class),
        };
    }

    private function findModeAndApply(CleanHTMLOperation $operation, StringValue $value): StringValue
    {
        foreach ($operation->getModes() as $mode) {
            $value = match ($mode) {
                CleanHTMLOperation::MODE_REMOVE_HTML_TAGS => new StringValue(strip_tags($value->getValue())),
                CleanHTMLOperation::MODE_DECODE_HTML_CHARACTERS => new StringValue(
                    html_entity_decode(htmlspecialchars_decode(str_replace('&nbsp;', ' ', $value->getValue()))),
                ),
                default => throw new \RuntimeException(sprintf('Unsupported clean HTML mode "%s"', $mode)),
            };
        }

        return $value;
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof CleanHTMLOperation;
    }
}
