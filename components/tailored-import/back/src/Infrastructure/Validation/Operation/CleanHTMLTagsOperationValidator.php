<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\Operation;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\CleanHTMLTagsOperation;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintValidator;

class CleanHTMLTagsOperationValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint): void
    {
        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($operation, new Collection([
                'fields' => [
                    'type' => new EqualTo(['value' => CleanHTMLTagsOperation::TYPE]),
                ],
            ]));
    }
}
