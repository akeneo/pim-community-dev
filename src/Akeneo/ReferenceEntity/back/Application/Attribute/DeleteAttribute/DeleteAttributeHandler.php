<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsImageReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\AttributeAsLabelReference;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsImageInterface;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\FindReferenceEntityAttributeAsLabelInterface;
use Akeneo\ReferenceEntity\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAttributeHandler
{
    /** @var FindReferenceEntityAttributeAsLabelInterface */
    private $findReferenceEntityAttributeAsLabel;

    /** @var FindReferenceEntityAttributeAsImageInterface */
    private $findReferenceEntityAttributeAsImage;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        FindReferenceEntityAttributeAsLabelInterface $findReferenceEntityAttributeAsLabel,
        FindReferenceEntityAttributeAsImageInterface $findReferenceEntityAttributeAsImage,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->findReferenceEntityAttributeAsLabel = $findReferenceEntityAttributeAsLabel;
        $this->findReferenceEntityAttributeAsImage = $findReferenceEntityAttributeAsImage;
        $this->attributeRepository = $attributeRepository;
    }

    public function __invoke(DeleteAttributeCommand $deleteAttributeCommand): void
    {
        $attributeIdentifier = AttributeIdentifier::fromString($deleteAttributeCommand->attributeIdentifier);
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);

        $labelReference = $this->findAttributeAsLabel($attribute->getReferenceEntityIdentifier());
        if (!$labelReference->isEmpty() && $labelReference->getIdentifier()->equals($attributeIdentifier)) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" cannot be deleted for the reference entity "%s"  as it is used as attribute as label.',
                    $attributeIdentifier,
                    $attribute->getReferenceEntityIdentifier()
                )
            );
        }

        $imageReference = $this->findAttributeAsImage($attribute->getReferenceEntityIdentifier());
        if (!$imageReference->isEmpty() && $imageReference->getIdentifier()->equals($attributeIdentifier)) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" cannot be deleted for the reference entity "%s"  as it is used as attribute as image.',
                    $attributeIdentifier,
                    $attribute->getReferenceEntityIdentifier()
                )
            );
        }

        $this->attributeRepository->deleteByIdentifier($attributeIdentifier);
    }

    private function findAttributeAsLabel(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeAsLabelReference
    {
        return ($this->findReferenceEntityAttributeAsLabel)($referenceEntityIdentifier);
    }

    private function findAttributeAsImage(ReferenceEntityIdentifier $referenceEntityIdentifier): AttributeAsImageReference
    {
        return ($this->findReferenceEntityAttributeAsImage)($referenceEntityIdentifier);
    }
}
