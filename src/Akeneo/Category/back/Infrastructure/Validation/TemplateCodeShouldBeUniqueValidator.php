<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Validation;

use Akeneo\Category\Application\Command\CreateTemplate\CreateTemplateCommand;
use Akeneo\Category\Application\Query\CheckTemplate;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TemplateCodeShouldBeUniqueValidator extends ConstraintValidator
{
    public function __construct(private readonly CheckTemplate $checkTemplate)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (empty($value)) {
            return;
        }
        Assert::isInstanceOf($constraint, TemplateCodeShouldBeUnique::class);

        /** @var CreateTemplateCommand $command */
        $command = $this->context->getObject();
        Assert::isInstanceOf($command, CreateTemplateCommand::class);

        $templateCode = $command->templateCode;

        if ($this->checkTemplate->codeExists(TemplateCode::fromString($templateCode))) {
            $this->context
                ->buildViolation($constraint->message, ['{{ templateCode }}' => $templateCode])
                ->addViolation();
        }
    }
}
