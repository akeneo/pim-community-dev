<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Source\Table;

use Akeneo\Platform\Syndication\Infrastructure\Validation\Operation\DefaultValueOperationConstraint;
use Akeneo\Platform\Syndication\Infrastructure\Validation\Source\SourceConstraintProvider;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\ConstraintValidator;

class TableSourceValidator extends ConstraintValidator
{
    public function validate($source, Constraint $constraint): void
    {
        $validator = $this->context->getValidator();
        $sourceConstraintFields = SourceConstraintProvider::getConstraintCollection()->fields;
        $sourceConstraintFields['selection'] = new Collection(['fields' => ['type' => new EqualTo(['value' => 'raw'])]]);
        $sourceConstraintFields['operations'] = new Collection(['fields' => [
            'default_value' => new Optional(new DefaultValueOperationConstraint()),
        ]]);

        $validator->inContext($this->context)->validate($source, new Collection(['fields' => $sourceConstraintFields]));
    }
}
