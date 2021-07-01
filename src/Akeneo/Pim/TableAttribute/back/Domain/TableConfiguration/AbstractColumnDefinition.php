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

abstract class AbstractColumnDefinition implements ColumnDefinition
{
    protected ColumnCode $code;
    protected ColumnDataType $dataType;
    protected LabelCollection $labels;
    protected ValidationCollection $validations;

    protected function __construct(
        ColumnCode $code,
        ColumnDataType $dataType,
        LabelCollection $labels,
        ValidationCollection $validations
    ) {
        $this->code = $code;
        $this->dataType = $dataType;
        $this->labels = $labels;
        $this->validations = $validations;
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

    public function normalize(): array
    {
        return [
            'code' => $this->code->asString(),
            'data_type' => $this->dataType->asString(),
            'labels' => $this->labels->normalize(),
            'validations' => $this->validations->normalize(),
        ];
    }
}
