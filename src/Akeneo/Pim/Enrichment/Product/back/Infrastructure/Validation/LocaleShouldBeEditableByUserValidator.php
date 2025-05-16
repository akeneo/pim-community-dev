<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\API\Query\IsLocaleEditable;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ValueUserIntent;
use Akeneo\Pim\Enrichment\Product\Domain\Model\ViolationCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LocaleShouldBeEditableByUserValidator extends ConstraintValidator
{
    public function __construct(private IsLocaleEditable $isLocaleEditable)
    {
    }

    public function validate($valueUserIntent, Constraint $constraint): void
    {
        Assert::isInstanceOf($valueUserIntent, ValueUserIntent::class);
        Assert::isInstanceOf($constraint, LocaleShouldBeEditableByUser::class);

        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        $localeCode = $valueUserIntent->localeCode();
        $userId = $command->userId();

        if (-1 === (int) $userId) {
            return;
        }

        if (!empty($localeCode) && !$this->isLocaleEditable->forUserId($localeCode, $userId)) {
            $this->context
                ->buildViolation($constraint->message, ['{{ locale_code }}' => $localeCode])
                ->setCode((string) ViolationCode::PERMISSION)
                ->addViolation();
        }
    }
}
