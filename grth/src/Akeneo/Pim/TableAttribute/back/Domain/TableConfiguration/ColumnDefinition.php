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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\IsRequiredForCompleteness;

interface ColumnDefinition
{
    public function id(): ColumnId;
    public function code(): ColumnCode;
    public function dataType(): ColumnDataType;
    public function labels(): LabelCollection;
    public function validations(): ValidationCollection;
    public function isRequiredForCompleteness(): IsRequiredForCompleteness;
    /** @return array<string, mixed> */
    public function normalize(): array;
}
