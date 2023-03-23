<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Domain\Query\DeactivateAttribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteAttributeCommandHandler
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly DeactivateAttribute $deactivateAttribute,
    ) {
    }

    public function __invoke(DeleteAttributeCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationsException($violations);
        }

        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $attributeUuid = AttributeUuid::fromString($command->attributeUuid);

        $this->deactivateAttribute->execute($templateUuid, $attributeUuid);
    }
}
