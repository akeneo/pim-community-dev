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

namespace Akeneo\AssetManager\Application\Attribute\DeleteAttribute;

use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsImageReference;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AttributeAsLabelReference;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeIdentifier;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsImageInterface;
use Akeneo\AssetManager\Domain\Query\AssetFamily\FindAssetFamilyAttributeAsLabelInterface;
use Akeneo\AssetManager\Domain\Repository\AttributeRepositoryInterface;

/**
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAttributeHandler
{
    /** @var FindAssetFamilyAttributeAsLabelInterface */
    private $findAssetFamilyAttributeAsLabel;

    /** @var FindAssetFamilyAttributeAsImageInterface */
    private $findAssetFamilyAttributeAsImage;

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        FindAssetFamilyAttributeAsLabelInterface $findAssetFamilyAttributeAsLabel,
        FindAssetFamilyAttributeAsImageInterface $findAssetFamilyAttributeAsImage,
        AttributeRepositoryInterface $attributeRepository
    ) {
        $this->findAssetFamilyAttributeAsLabel = $findAssetFamilyAttributeAsLabel;
        $this->findAssetFamilyAttributeAsImage = $findAssetFamilyAttributeAsImage;
        $this->attributeRepository = $attributeRepository;
    }

    public function __invoke(DeleteAttributeCommand $deleteAttributeCommand): void
    {
        $attributeIdentifier = AttributeIdentifier::fromString($deleteAttributeCommand->attributeIdentifier);
        $attribute = $this->attributeRepository->getByIdentifier($attributeIdentifier);

        $labelReference = $this->findAttributeAsLabel($attribute->getAssetFamilyIdentifier());
        if (!$labelReference->isEmpty() && $labelReference->getIdentifier()->equals($attributeIdentifier)) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" cannot be deleted for the asset family "%s"  as it is used as attribute as label.',
                    $attributeIdentifier,
                    $attribute->getAssetFamilyIdentifier()
                )
            );
        }

        $imageReference = $this->findAttributeAsImage($attribute->getAssetFamilyIdentifier());
        if (!$imageReference->isEmpty() && $imageReference->getIdentifier()->equals($attributeIdentifier)) {
            throw new \LogicException(
                sprintf(
                    'Attribute "%s" cannot be deleted for the asset family "%s"  as it is used as attribute as image.',
                    $attributeIdentifier,
                    $attribute->getAssetFamilyIdentifier()
                )
            );
        }

        $this->attributeRepository->deleteByIdentifier($attributeIdentifier);
    }

    private function findAttributeAsLabel(AssetFamilyIdentifier $assetFamilyIdentifier): AttributeAsLabelReference
    {
        return $this->findAssetFamilyAttributeAsLabel->find($assetFamilyIdentifier);
    }

    private function findAttributeAsImage(AssetFamilyIdentifier $assetFamilyIdentifier): AttributeAsImageReference
    {
        return $this->findAssetFamilyAttributeAsImage->find($assetFamilyIdentifier);
    }
}
