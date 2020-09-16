<?php

declare(strict_types=1);

namespace Akeneo\Platform\Component\EventQueue;

/**
 * @copyright 202O Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface BusinessEventInterface
{
    public function name(): string;

    public function author(): string;

    public function data(): array;

    public function timestamp(): int;

    public function uuid(): string;
}
