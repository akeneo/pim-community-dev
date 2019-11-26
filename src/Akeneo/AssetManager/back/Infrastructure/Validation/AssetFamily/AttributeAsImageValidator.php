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
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
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
class AttributeAsImageValidator extends ConstraintValidator
{
    private const VALID_ATTRIBUTE_TYPES = [MediaLinkAttribute::ATTRIBUTE_TYPE, ImageAttribute::ATTRIBUTE_TYPE];

    /** @var AttributeExistsInterface */
    private $attributeExists;

    /** @var GetAttributeTypeInterface */
    private $getAttributeType;

    public function __construct(AttributeExistsInterface $attributeExists, GetAttributeTypeInterface $getAttributeType)
    {
        $this->attributeExists = $attributeExists;
        $this->getAttributeType = $getAttributeType;
    }

    public function validate($editAssetFamilyCommand, Constraint $constraint)
    {
        if (!$constraint instanceof AttributeAsImage) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        // We validate only if the attributeAsImage is present
        if (null !== $editAssetFamilyCommand->attributeAsImage) {
            $this->validateAttributeExists($editAssetFamilyCommand) &&
            $this->validateAttributeType($editAssetFamilyCommand);
        }
    }

    private function validateAttributeExists(EditAssetFamilyCommand $editAssetFamilyCommand)
    {
        $attributeExists = $this->attributeExists->withAssetFamilyAndCode(
            AssetFamilyIdentifier::fromString($editAssetFamilyCommand->identifier),
            AttributeCode::fromString($editAssetFamilyCommand->attributeAsImage)
        );

        if (!$attributeExists) {
            $this->context->buildViolation(AttributeAsImage::ATTRIBUTE_NOT_FOUND)
                ->setParameter('%attribute_as_image%', $editAssetFamilyCommand->attributeAsImage)
                ->atPath('attribute_as_image')
                ->addViolation();
        }

        return $attributeExists;
    }

    private function validateAttributeType(EditAssetFamilyCommand $editAssetFamilyCommand)
    {
        $attributeType = $this->getAttributeType->fetch(
            AssetFamilyIdentifier::fromString($editAssetFamilyCommand->identifier),
            AttributeCode::fromString($editAssetFamilyCommand->attributeAsImage)
        );

        if (!in_array($attributeType, self::VALID_ATTRIBUTE_TYPES)) {
            $this->context->buildViolation(AttributeAsImage::INVALID_ATTRIBUTE_TYPE)
                ->setParameter('%valid_attribute_as_image_types%', implode(', ', self::VALID_ATTRIBUTE_TYPES))
                ->atPath('attribute_as_image')
                ->addViolation();
        }
    }
}
