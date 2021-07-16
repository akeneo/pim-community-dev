<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;

interface ColumnDefinition
{
    public function code(): ColumnCode;
    public function dataType(): ColumnDataType;
    public function labels(): LabelCollection;
    public function validations(): ValidationCollection;
    /** @return array<string, mixed> */
    public function normalize(): array;
}
