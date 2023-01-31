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
 */
final class IdentifierGenerator
{
    public function __construct(
        private IdentifierGeneratorId $id,
        private IdentifierGeneratorCode $code,
        private Conditions $conditions,
        private Structure $structure,
        private LabelCollection $labelCollection,
        private Target $target,
        private ?Delimiter $delimiter,
        private TextTransformation $textTransformation,
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

    public function delimiter(): ?Delimiter
    {
        return $this->delimiter;
    }

    public function setStructure(Structure $structure): void
    {
        $this->structure = $structure;
    }

    public function setConditions(Conditions $conditions): void
    {
        $this->conditions = $conditions;
    }

    public function setLabelCollection(LabelCollection $labelCollection): void
    {
        $this->labelCollection = $labelCollection;
    }

    public function setTarget(Target $target): void
    {
        $this->target = $target;
    }

    public function setDelimiter(?Delimiter $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @return array{
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
    public function normalize(): array
    {
        return [
            'uuid' => $this->id->asString(),
            'code' => $this->code->asString(),
            'conditions' => $this->conditions->normalize(),
            'structure' => $this->structure->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'target' => $this->target->asString(),
            'delimiter' => $this->delimiter?->asString(),
            'text_transformation' => $this->textTransformation->normalize(),
        ];
    }

    public function match(ProductProjection $productProjection): bool
    {
        $conditionsWithImplicitOnes = $this->conditions->and($this->getImplicitConditions());

        return $conditionsWithImplicitOnes->match($productProjection);
    }

    /**
     * @return ConditionInterface[]
     */
    private function getImplicitConditions(): array
    {
        $conditions = [new EmptyIdentifier($this->target()->asString())];

        return \array_merge($conditions, $this->structure->getImplicitConditions());
    }

    public function textTransformation(): TextTransformation
    {
        return $this->textTransformation;
    }

    public function setTextTransformation(TextTransformation $textTransformation): void
    {
        $this->textTransformation = $textTransformation;
    }
}
