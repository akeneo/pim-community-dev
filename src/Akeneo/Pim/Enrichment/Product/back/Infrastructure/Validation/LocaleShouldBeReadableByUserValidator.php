<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Infrastructure\Validation;

use Akeneo\Channel\API\Query\IsLocaleReadable;
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
final class LocaleShouldBeReadableByUserValidator extends ConstraintValidator
{
    public function __construct(private IsLocaleReadable $isLocaleReadable)
    {
    }

    public function validate($valueUserIntent, Constraint $constraint): void
    {
        Assert::isInstanceOf($valueUserIntent, ValueUserIntent::class);
        Assert::isInstanceOf($constraint, LocaleShouldBeReadableByUser::class);

        $command = $this->context->getRoot();
        Assert::isInstanceOf($command, UpsertProductCommand::class);

        $localeCode = $valueUserIntent->localeCode();
        $userId = $command->userId();
        if (!empty($localeCode) && !$this->isLocaleReadable->forUserId($localeCode, $userId)) {
            $this->context
                ->buildViolation($constraint->message, ['{{ locale_code }}' => $localeCode])
                ->setCode((string) ViolationCode::PERMISSION)
                ->addViolation();
        }
    }
}
