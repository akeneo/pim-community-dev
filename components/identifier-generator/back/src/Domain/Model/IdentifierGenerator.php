<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\ConditionInterface;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\EmptyIdentifier;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ConditionsNormalized from Conditions
 * @phpstan-import-type StructureNormalized from Structure
 * @phpstan-import-type LabelsNormalized from LabelCollection
 * @phpstan-import-type TextTransformationNormalized from TextTransformation
 * @phpstan-type NormalizedIdentifierGenerator array{
 *     uuid: string,
 *     code: string,
 *     conditions: ConditionsNormalized,
 *     structure: StructureNormalized,
 *     labels: LabelsNormalized,
 *     target: string,
 *     delimiter: string | null,
 *     text_transformation: TextTransformationNormalized
 * }
 */
final class IdentifierGenerator
{
    public function __construct(
        private readonly IdentifierGeneratorId $id,
        private readonly IdentifierGeneratorCode $code,
        private readonly Conditions $conditions,
        private readonly Structure $structure,
        private readonly LabelCollection $labelCollection,
        private readonly Target $target,
        private readonly Delimiter $delimiter,
        private readonly TextTransformation $textTransformation,
    ) {
    }

    public function id(): IdentifierGeneratorId
    {
        return $this->id;
    }

    public function code(): IdentifierGeneratorCode
    {
        return $this->code;
    }

    public function conditions(): Conditions
    {
        return $this->conditions;
    }

    public function structure(): Structure
    {
        return $this->structure;
    }

    public function labelCollection(): LabelCollection
    {
        return $this->labelCollection;
    }

    public function target(): Target
    {
        return $this->target;
    }

    public function delimiter(): Delimiter
    {
        return $this->delimiter;
    }

    public function textTransformation(): TextTransformation
    {
        return $this->textTransformation;
    }

    public function withStructure(Structure $structure): self
    {
        return new IdentifierGenerator(
            $this->id,
            $this->code,
            $this->conditions,
            $structure,
            $this->labelCollection,
            $this->target,
            $this->delimiter,
            $this->textTransformation
        );
    }

    public function withConditions(Conditions $conditions): self
    {
        return new IdentifierGenerator(
            $this->id,
            $this->code,
            $conditions,
            $this->structure,
            $this->labelCollection,
            $this->target,
            $this->delimiter,
            $this->textTransformation
        );
    }

    public function withLabelCollection(LabelCollection $labelCollection): self
    {
        return new IdentifierGenerator(
            $this->id,
            $this->code,
            $this->conditions,
            $this->structure,
            $labelCollection,
            $this->target,
            $this->delimiter,
            $this->textTransformation
        );
    }

    public function withTarget(Target $target): self
    {
        return new IdentifierGenerator(
            $this->id,
            $this->code,
            $this->conditions,
            $this->structure,
            $this->labelCollection,
            $target,
            $this->delimiter,
            $this->textTransformation
        );
    }

    public function withDelimiter(Delimiter $delimiter): self
    {
        return new IdentifierGenerator(
            $this->id,
            $this->code,
            $this->conditions,
            $this->structure,
            $this->labelCollection,
            $this->target,
            $delimiter,
            $this->textTransformation
        );
    }

    public function withTextTransformation(TextTransformation $textTransformation): self
    {
        return new IdentifierGenerator(
            $this->id,
            $this->code,
            $this->conditions,
            $this->structure,
            $this->labelCollection,
            $this->target,
            $this->delimiter,
            $textTransformation
        );
    }

    /**
     * @return NormalizedIdentifierGenerator
     */
    public function normalize(): array
    {
        return [
            'uuid' => $this->id->asString(),
            'code' => $this->code->asString(),
            'conditions' => $this->conditions->normalize(),
            'structure' => $this->structure->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'target' => $this->target->asString(),
            'delimiter' => $this->delimiter->asString(),
            'text_transformation' => $this->textTransformation->normalize(),
        ];
    }

    /**
     * @return ConditionInterface[]
     */
    public function getImplicitConditions(): array
    {
        $conditions = [new EmptyIdentifier($this->target()->asString())];

        return \array_merge($conditions, $this->structure->getImplicitConditions());
    }
}
