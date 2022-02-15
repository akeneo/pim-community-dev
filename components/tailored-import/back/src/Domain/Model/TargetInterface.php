<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Domain\Model;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TargetInterface
{
    public const ACTION_ADD = 'add';
    public const ACTION_SET = 'set';
    public const IF_EMPTY_CLEAR = 'clear';
    public const IF_EMPTY_SKIP = 'skip';

    public function code(): string;
    public function action(): string;
    public function ifEmpty(): string;
}
