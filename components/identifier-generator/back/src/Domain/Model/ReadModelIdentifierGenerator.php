<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReadModelIdentifierGenerator
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
}
