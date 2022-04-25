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

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\TailoredImport\Domain\Model\Operation\OperationInterface;

class CleanHTMLTagsOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, string $value): string
    {
        if (!$operation instanceof CleanHTMLTagsOperation) {
            throw new \InvalidArgumentException(sprintf('Expecting Clean HTML Tags Operation, "%s" given', $operation::class));
        }

        return strip_tags(
            htmlspecialchars_decode(
                str_replace('&nbsp;', ' ', $value),
            ),
        );
    }

    public function supports(OperationInterface $operation): bool
    {
        return $operation instanceof CleanHTMLTagsOperation;
    }
}
