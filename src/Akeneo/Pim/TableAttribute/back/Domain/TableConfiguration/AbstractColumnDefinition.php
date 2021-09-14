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
use Webmozart\Assert\Assert;

abstract class AbstractColumnDefinition implements ColumnDefinition
{
    public const DATATYPE = '';

    protected ColumnId $id;
    protected ColumnCode $code;
    protected ColumnDataType $dataType;
    protected LabelCollection $labels;
    protected ValidationCollection $validations;

    final protected function __construct(
        ColumnId $id,
        ColumnCode $code,
        ColumnDataType $dataType,
        LabelCollection $labels,
        ValidationCollection $validations
    ) {
        $this->id = $id;
        $this->code = $code;
        $this->dataType = $dataType;
        $this->labels = $labels;
        $this->validations = $validations;
    }

    public function id(): ColumnId
    {
        return $this->id;
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

    public function validations(): ValidationCollection
    {
        return $this->validations;
    }

    /**
     * @param array<string, mixed> $normalized
     */
    public static function fromNormalized(array $normalized): self
    {
        Assert::keyExists($normalized, 'id');
        Assert::keyExists($normalized, 'code');
        $dataType = ColumnDataType::fromString(static::DATATYPE);

        return new static(
            ColumnId::fromString($normalized['id']),
            ColumnCode::fromString($normalized['code']),
            $dataType,
            LabelCollection::fromNormalized($normalized['labels'] ?? []),
            ValidationCollection::fromNormalized($dataType, $normalized['validations'] ?? [])
        );
    }

    public function normalize(): array
    {
        return [
            'id' => $this->id->asString(),
            'code' => $this->code->asString(),
            'data_type' => $this->dataType->asString(),
            'labels' => $this->labels->normalize(),
            'validations' => $this->validations->normalize(),
        ];
    }
}
