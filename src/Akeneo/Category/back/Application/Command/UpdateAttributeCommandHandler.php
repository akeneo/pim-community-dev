<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateAttributeCommandHandler
{
    public function __construct(
        private readonly GetAttribute $getAttribute,
        private readonly CategoryTemplateAttributeSaver $categoryTemplateAttributeSaver,
    ) {
    }

    public function __invoke(UpdateAttributeCommand $command): void
    {
        $attributeUuid = AttributeUuid::fromString($command->attributeUuid);
        $attribute = $this->getAttribute->byUuid($attributeUuid);
        if ($attribute === null) {
            throw new \InvalidArgumentException(sprintf('Attribute with uuid: %s does not exist', $command->attributeUuid));
        }

        $data = [];
        if ($command->isRichTextArea !== null) {
            $data['isRichRextArea'] = $command->isRichTextArea;
        }

        if ($command->labels !== null) {
            $data['labels'] = $command->labels;
        }

        $attribute->update($data);
        $this->categoryTemplateAttributeSaver->update($attribute);
    }
}
