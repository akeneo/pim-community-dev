<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Pim\Enrichment\Component\Product\Exception\ObjectNotFoundException;
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

        $attributeUuid = AttributeUuid::fromString($command->attributeUuid);
        $attributes = $this->getAttribute->byUuids([$attributeUuid]);
        if($attributes->count() == 0) {
            throw new ObjectNotFoundException();
        }

        /** @var Attribute $attribute */
        $attribute = $attributes->getAttributes()[0];

        $data = [
          'is_rich_text_area'  => $command->isRichTextArea,
        ];
        $attribute->update($data);
        $this->categoryTemplateAttributeSaver->update($attribute);
    }
}
