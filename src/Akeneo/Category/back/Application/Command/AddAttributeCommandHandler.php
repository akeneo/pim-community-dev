<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Exception\ViolationsException;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeAdditionalProperties;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCollection;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsLocalizable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsRequired;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIsScopable;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeType;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeCommandHandler
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly GetAttribute $getAttribute,
        private readonly CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver,
    ) {
    }

    public function __invoke(AddAttributeCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationsException($violations);
        }

        $templateUuid = TemplateUuid::fromString($command->templateUuid);

        $attribute = Attribute::fromType(
            type: new AttributeType($command->type),
            uuid: AttributeUuid::fromUuid(Uuid::uuid4()),
            code: new AttributeCode($command->code),
            order: $this->getAttributeOrder($templateUuid),
            isRequired: AttributeIsRequired::fromBoolean(false),
            isScopable: AttributeIsScopable::fromBoolean($command->isScopable),
            isLocalizable: AttributeIsLocalizable::fromBoolean($command->isLocalizable),
            labelCollection: $this->getAttributeLabel($command->locale, $command->label),
            templateUuid: $templateUuid,
            additionalProperties: AttributeAdditionalProperties::fromArray([]),
        );

        $this->categoryTemplateAttributeSaver->insert($templateUuid, AttributeCollection::fromArray([$attribute]));
    }

    private function getAttributeOrder(TemplateUuid $templateUuid): AttributeOrder
    {
        $attributes = $this->getAttribute->byTemplateUuid($templateUuid);

        return AttributeOrder::fromInteger($attributes->calculateNextOrder());
    }

    private function getAttributeLabel(string $locale, ?string $label = null): LabelCollection
    {
        if ($label === null || $label === '') {
            return LabelCollection::fromArray([]);
        }

        return LabelCollection::fromArray([$locale => $label]);
    }
}
