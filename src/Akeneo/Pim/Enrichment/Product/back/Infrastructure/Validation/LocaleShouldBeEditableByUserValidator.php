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

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\API\Query\GetEditableLocaleCodes;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class LocaleShouldBeEditableByUserValidator extends ConstraintValidator
{
    public function __construct(private GetEditableLocaleCodes $getEditableLocaleCodes)
    {
    }

    public function validate($command, Constraint $constraint): void
    {
        Assert::isInstanceOf($command, UpsertProductCommand::class);
        Assert::isInstanceOf($constraint, LocaleShouldBeEditableByUser::class);

        $userEditableLocaleCodes = $this->getEditableLocaleCodes->forUserId($command->userId());

        foreach ($command->valuesUserIntent() as $valueUserIntent) {
            $localCode = $valueUserIntent->localeCode();
            if (!\in_array($valueUserIntent->localeCode(), $userEditableLocaleCodes)) {
                $this->context->buildViolation($constraint->message, ['{{ locale_code }}' => $localCode])->addViolation();
            }
        }
    }
}
