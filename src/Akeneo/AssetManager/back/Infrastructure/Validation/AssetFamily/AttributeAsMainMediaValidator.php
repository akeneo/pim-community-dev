<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Infrastructure\Validation\AssetFamily;

use Akeneo\AssetManager\Application\AssetFamily\EditAssetFamily\EditAssetFamilyCommand;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeCode;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaFileAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\MediaLinkAttribute;
use Akeneo\AssetManager\Domain\Query\Attribute\AttributeExistsInterface;
use Akeneo\AssetManager\Domain\Query\Attribute\GetAttributeTypeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class AttributeAsMainMediaValidator extends ConstraintValidator
{
    private const VALID_ATTRIBUTE_TYPES = [MediaLinkAttribute::ATTRIBUTE_TYPE, MediaFileAttribute::ATTRIBUTE_TYPE];

    private AttributeExistsInterface $attributeExists;

    private GetAttributeTypeInterface $getAttributeType;

    public function __construct(AttributeExistsInterface $attributeExists, GetAttributeTypeInterface $getAttributeType)
    {
        $this->attributeExists = $attributeExists;
        $this->getAttributeType = $getAttributeType;
    }

    public function validate($editAssetFamilyCommand, Constraint $constraint)
    {
        if (!$constraint instanceof AttributeAsMainMedia) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        // We validate only if the attributeAsMainMedia is present
        if (null !== $editAssetFamilyCommand->attributeAsMainMedia) {
            $this->validateAttributeExists($editAssetFamilyCommand) &&
            $this->validateAttributeType($editAssetFamilyCommand);
        }
    }

    private function validateAttributeExists(EditAssetFamilyCommand $editAssetFamilyCommand)
    {
        $attributeExists = $this->attributeExists->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($editAssetFamilyCommand->identifier),
            AttributeCode::fromString($editAssetFamilyCommand->attributeAsMainMedia)
        );

        if (!$attributeExists) {
            $this->context->buildViolation(AttributeAsMainMedia::ATTRIBUTE_NOT_FOUND)
                ->setParameter('%attribute_as_main_media%', $editAssetFamilyCommand->attributeAsMainMedia)
                ->atPath('attribute_as_main_media')
                ->addViolation();
        }

        return $attributeExists;
    }

    private function validateAttributeType(EditAssetFamilyCommand $editAssetFamilyCommand)
    {
        $attributeType = $this->getAttributeType->fetch(
            AssetFamilyIdentifier::fromString($editAssetFamilyCommand->identifier),
            AttributeCode::fromString($editAssetFamilyCommand->attributeAsMainMedia)
        );

        if (!in_array($attributeType, self::VALID_ATTRIBUTE_TYPES)) {
            $this->context->buildViolation(AttributeAsMainMedia::INVALID_ATTRIBUTE_TYPE)
                ->setParameter('%valid_attribute_as_main_media_types%', implode(', ', self::VALID_ATTRIBUTE_TYPES))
                ->atPath('attribute_as_main_media')
                ->addViolation();
        }
    }
}
