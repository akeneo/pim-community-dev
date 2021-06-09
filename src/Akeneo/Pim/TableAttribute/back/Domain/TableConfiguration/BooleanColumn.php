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
use Webmozart\Assert\Assert;

class BooleanColumn extends AbstractColumnDefinition
{
    private const DATATYPE = 'boolean';

    // validation for number (min, max decimal allowed)

    /**
     * @param array<string, mixed> $normalized
     */
    public static function fromNormalized(array $normalized): self
    {
        Assert::keyExists($normalized, 'code');

        $labels = $normalized['labels'] ?? [];
        Assert::isArray($labels);

        $validations = $normalized['validations'] ?? [];
        Assert::isArray($validations);

        return new self(
            ColumnCode::fromString($normalized['code']),
            ColumnDataType::fromString(self::DATATYPE),
            LabelCollection::fromNormalized($labels),
            ValidationCollection::fromNormalized($validations)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function normalize(): array
    {
        // TODO Check if we can't abstract this normalize() method
        $labels = $this->labels->labels();
        $validations = $this->validations->normalize();

        return [
            'code' => $this->code->asString(),
            'data_type' => $this->dataType->asString(),
            'labels' => [] === $labels ? (object) [] : $labels,
            'validations' => [] === $validations ? (object) [] : $validations,
        ];
    }
}
