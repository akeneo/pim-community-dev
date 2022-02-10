<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Application\Common;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TargetInterface
{
    public function code(): string;
    public function action(): string;
    public function ifEmpty(): string;
}
