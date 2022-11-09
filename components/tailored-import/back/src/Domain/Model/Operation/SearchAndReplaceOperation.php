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

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

use Webmozart\Assert\Assert;

final class SearchAndReplaceOperation implements OperationInterface
{
    public const TYPE = 'search_and_replace';

    /**
     * @param SearchAndReplaceValue[] $replacements
     */
    public function __construct(
        private string $uuid,
        private array $replacements,
    ) {
        Assert::uuid($uuid);
        Assert::allIsInstanceOf($replacements, SearchAndReplaceValue::class);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return SearchAndReplaceValue[]
     */
    public function getReplacements(): array
    {
        return $this->replacements;
    }

    public function normalize(): array
    {
        return [
            'uuid' => $this->uuid,
            'type' => self::TYPE,
            'replacements' => array_map(
                static fn (SearchAndReplaceValue $replacement) => $replacement->normalize(),
                $this->replacements,
            ),
        ];
    }
}
