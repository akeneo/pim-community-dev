<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\TaskScheduling\Domain\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class TaskCode
{
    private string $code;

    private function __construct(string $code)
    {
        $this->code = $code;
    }

    public static function fromString(string $code): TaskCode
    {
        Assert::stringNotEmpty($code, 'Task code should be a non empty string');
        Assert::maxLength(
            $code,
            255,
            'Task code cannot be longer than 255 characters'
        );
        Assert::regex(
            $code,
            '/^[a-zA-Z0-9_]+$/',
            sprintf('Task code may contain only letters, numbers and underscores. "%s" given', $code)
        );

        return new self($code);
    }

    public function asString(): string
    {
        return $this->code;
    }

    public function equals(TaskCode $otherCode): bool
    {
        return $this->code === $otherCode->asString();
    }
}
