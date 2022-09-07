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

final class CleanHTMLOperation implements OperationInterface
{
    public const TYPE = 'clean_html';

    public const MODE_REMOVE_HTML_TAGS = 'remove';
    public const MODE_DECODE_HTML_CHARACTERS = 'decode';

    public function __construct(
        private string $uuid,
        private array $modes,
    ) {
        Assert::uuid($uuid);
        Assert::notEmpty($modes);
        foreach ($modes as $mode) {
            Assert::oneOf($mode, [self::MODE_REMOVE_HTML_TAGS, self::MODE_DECODE_HTML_CHARACTERS]);
        }
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getModes(): array
    {
        return $this->modes;
    }

    public function normalize(): array
    {
        return [
            'uuid' => $this->uuid,
            'modes' => $this->modes,
            'type' => self::TYPE,
        ];
    }
}
