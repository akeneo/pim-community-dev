<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Event\AttributeDeactivatedEvent;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\Query\DeactivateAttribute;
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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateAttributeCommandHandler
{
    public function __construct(
        private readonly ValidatorInterface $validator,
        private readonly GetAttribute $getAttribute,
        private readonly CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver,
    ) {
    }

    public function __invoke(UpdateAttributeCommand $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ViolationsException($violations);
        }

        $templateUuid = TemplateUuid::fromString($command->templateUuid);
        $attributeUuid = AttributeUuid::fromString($command->attributeUuid);

        $attribute = Attribute::fromType(
            type: new AttributeType($command->type),
            uuid: $attributeUuid,
            code: new AttributeCode($command->code),
            order: AttributeOrder::fromInteger($command->order),
            isRequired: AttributeIsRequired::fromBoolean($command->isRequired),
            isScopable: AttributeIsScopable::fromBoolean($command->isScopable),
            isLocalizable: AttributeIsLocalizable::fromBoolean($command->isLocalizable),
            labelCollection: $this->getAttributeLabel($command->locale, $command->label),
            templateUuid: $templateUuid,
            additionalProperties: AttributeAdditionalProperties::fromArray([]),
        );

        $this->categoryTemplateAttributeSaver->update($templateUuid, $attribute);
    }

    private function getAttributeLabel(string $locale, ?string $label = null): LabelCollection
    {
        if ($label === null || $label === '') {
            return LabelCollection::fromArray([]);
        }

        return LabelCollection::fromArray([$locale => $label]);
    }
}
