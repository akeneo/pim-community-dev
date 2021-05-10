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

namespace Akeneo\Pim\TableAttribute\Domain\Config;

use Akeneo\Pim\TableAttribute\Domain\Config\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\Config\ValueObject\ColumnDataType;

abstract class ColumnDefinition
{
    protected ColumnCode $code;
    protected ColumnDataType $dataType;
    protected LabelCollection $labels;

    // validation rules: specific to each data type

    protected function __construct(ColumnCode $code, ColumnDataType $dataType, LabelCollection $labels)
    {
        $this->code = $code;
        $this->dataType = $dataType;
        $this->labels = $labels;
    }

    public function code(): ColumnCode
    {
        return $this->code;
    }

    public function dataType(): ColumnDataType
    {
        return $this->dataType;
    }

    public function labels(): LabelCollection
    {
        return $this->labels;
    }
}
