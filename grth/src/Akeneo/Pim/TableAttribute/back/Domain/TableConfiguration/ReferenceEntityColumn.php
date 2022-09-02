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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ReferenceEntityIdentifier;
use Webmozart\Assert\Assert;

final class ReferenceEntityColumn extends AbstractColumnDefinition
{
    public const DATATYPE = 'reference_entity';

    private ReferenceEntityIdentifier $referenceEntityIdentifier;

    final protected function __construct(
        ColumnId $id,
        ColumnCode $code,
        ColumnDataType $dataType,
        LabelCollection $labels,
        ValidationCollection $validations,
        IsRequiredForCompleteness $isRequiredForCompleteness,
        ReferenceEntityIdentifier $referenceEntityIdentifier
    ) {
        parent::__construct($id, $code, $dataType, $labels, $validations, $isRequiredForCompleteness);
        $this->referenceEntityIdentifier = $referenceEntityIdentifier;
    }

    /**
     * @param array<string, mixed> $normalized
     */
    public static function fromNormalized(array $normalized): self
    {
        Assert::keyExists($normalized, 'id');
        Assert::keyExists($normalized, 'code');
        Assert::keyExists($normalized, 'reference_entity_identifier');
        $dataType = ColumnDataType::fromString(static::DATATYPE);

        return new self(
            ColumnId::fromString($normalized['id']),
            ColumnCode::fromString($normalized['code']),
            $dataType,
            LabelCollection::fromNormalized($normalized['labels'] ?? []),
            ValidationCollection::fromNormalized($dataType, $normalized['validations'] ?? []),
            IsRequiredForCompleteness::fromBoolean($normalized['is_required_for_completeness'] ?? false),
            ReferenceEntityIdentifier::fromString($normalized['reference_entity_identifier'])
        );
    }

    public function normalize(): array
    {
        $normalized = parent::normalize();
        $normalized['reference_entity_identifier'] = $this->referenceEntityIdentifier->asString();

        return $normalized;
    }

    public function referenceEntityIdentifier(): ReferenceEntityIdentifier
    {
        return $this->referenceEntityIdentifier;
    }
}
