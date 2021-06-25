<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Selection;

use Symfony\Component\Validator\Constraint;

class CodeLabelCollectionSelection extends Constraint
{
    public function validatedBy()
    {
        return 'akeneo.tailored_export.validation.source.code_label_collection_selection';
    }
}
