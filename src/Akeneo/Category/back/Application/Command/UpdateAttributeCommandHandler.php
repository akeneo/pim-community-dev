<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command;

use Akeneo\Category\Api\Command\Exceptions\ViolationsException;
use Akeneo\Category\Application\Query\GetAttribute;
use Akeneo\Category\Application\Storage\Save\Saver\CategoryTemplateAttributeSaver;
use Akeneo\Category\Domain\Model\Attribute\Attribute;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeUuid;
use Symfony\Component\Validator\ConstraintViolation;
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

        $attributeUuid = AttributeUuid::fromString($command->attributeUuid);
        $attributes = $this->getAttribute->byUuids([$attributeUuid]);
        if ($attributes->count() == 0) {
            throw new \InvalidArgumentException(sprintf('Attribute with uuid: %s does not exist', $command->attributeUuid));
        }

        /** @var Attribute $attribute */
        $attribute = $attributes->getAttributes()[0];

        $data = [];
        if ($command->isRichTextArea !== null) {
            $data['isRichRextArea'] = $command->isRichTextArea;
        }

        if ($command->labels !== null) {
            $data['labels'] = $command->labels;
        }

        try {
            $attribute->update($data);
        } catch (\LogicException $exception) {
            $violations->add(new ConstraintViolation($exception->getMessage(), $exception->getMessage(), [], null, 'type', null));
        }

        if ($violations->count() > 0) {
            throw new ViolationsException($violations);
        }

        $this->categoryTemplateAttributeSaver->update($attribute);
    }
}
