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

final class ChangeCaseOperation implements OperationInterface
{
    public const TYPE = 'change_case';

    public const MODE_CAPITALIZE = 'capitalize';
    public const MODE_UPPERCASE = 'uppercase';
    public const MODE_LOWERCASE = 'lowercase';

    public function __construct(
        private string $uuid,
        private string $mode,
    ) {
        Assert::uuid($uuid);
        Assert::inArray($mode, [self::MODE_CAPITALIZE, self::MODE_LOWERCASE, self::MODE_UPPERCASE]);
    }

    public function getUuid(): string
    {
        return $this->uuid;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function normalize(): array
    {
        return [
            'uuid' => $this->uuid,
            'mode' => $this->mode,
            'type' => self::TYPE,
        ];
    }
}
