<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
