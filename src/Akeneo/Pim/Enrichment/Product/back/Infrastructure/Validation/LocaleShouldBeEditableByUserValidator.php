<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\API\Query\GetEditableLocaleCodes;
use Akeneo\Pim\Enrichment\Product\Api\Command\UpsertProductCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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
            $localeCode = $valueUserIntent->localeCode();
            if (!empty($localeCode) && !\in_array($valueUserIntent->localeCode(), $userEditableLocaleCodes)) {
                $this->context->buildViolation($constraint->message, ['{{ locale_code }}' => $localeCode])->addViolation();
            }
        }
    }
}
