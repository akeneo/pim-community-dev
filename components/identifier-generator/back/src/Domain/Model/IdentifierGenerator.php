<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Identifier;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type ConditionsNormalized from Conditions
 * @phpstan-import-type StructureNormalized from Structure
 * @phpstan-import-type LabelsNormalized from LabelCollection
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
    ) {
        foreach ($this->structure->getProperties() as $property) {
            $autoCondition = $property->getAutoCondition();
            if (null !== $autoCondition) {
                $this->conditions = $this->conditions->push($autoCondition);
            }
        }

        $this->conditions = $this->conditions->push(new Identifier());
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
     * }
     */
    public function normalizeForFront(): array
    {
        return [
            'uuid' => $this->id->asString(),
            'code' => $this->code->asString(),
            'conditions' => $this->conditions->normalize(true),
            'structure' => $this->structure->normalize(),
            'labels' => $this->labelCollection->normalize(),
            'target' => $this->target->asString(),
            'delimiter' => $this->delimiter?->asString(),
        ];
    }

    public function match(ProductProjection $productProjection): bool
    {
        return $this->conditions->match($productProjection);
    }

    public function nonAutoConditions(): Conditions
    {
        return $this->conditions->nonAuto();
    }
}
