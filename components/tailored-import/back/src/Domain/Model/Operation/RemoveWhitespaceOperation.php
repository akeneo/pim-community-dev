<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Domain\Model\Operation;

use Webmozart\Assert\Assert;

final class RemoveWhitespaceOperation implements OperationInterface
{
    public const TYPE = 'remove_whitespace';

    public const MODE_CONSECUTIVE = 'consecutive';
    public const MODE_TRIM = 'trim';

    public function __construct(
        private string $uuid,
        private array $modes,
    ) {
        Assert::uuid($uuid);
        Assert::notEmpty($modes);
        foreach ($modes as $mode) {
            Assert::oneOf($mode, [self::MODE_CONSECUTIVE, self::MODE_TRIM]);
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
