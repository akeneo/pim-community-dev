<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Application\MapValues\OperationApplier;

use Akeneo\Platform\Syndication\Application\Common\Operation\CleanHTMLTagsOperation;
use Akeneo\Platform\Syndication\Application\Common\Operation\OperationInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\SourceValueInterface;
use Akeneo\Platform\Syndication\Application\Common\SourceValue\StringValue;

class CleanHTMLTagsOperationApplier implements OperationApplierInterface
{
    public function applyOperation(OperationInterface $operation, SourceValueInterface $value): SourceValueInterface
    {
        if (!$operation instanceof CleanHTMLTagsOperation || !$value instanceof StringValue) {
            throw new \InvalidArgumentException('Cannot apply Clean HTML tags operation');
        }

        return new StringValue(
            strip_tags(
                htmlspecialchars_decode(
                    str_replace('&nbsp;', ' ', $this->removeTags($value->getData()))
                )
            )
        );
    }

    private function removeTags(string $string): string
    {
        $openTagPosition = strpos($string, '<style');
        $closeTagPosition = strpos($string, '/style>');

        return $openTagPosition === false || $closeTagPosition === false
            ? $string
            : $this->removeTags(substr($string, 0, $openTagPosition) . substr($string, $closeTagPosition + 7));
    }

    public function supports(OperationInterface $operation, SourceValueInterface $value): bool
    {
        return $operation instanceof CleanHTMLTagsOperation && $value instanceof StringValue;
    }
}
