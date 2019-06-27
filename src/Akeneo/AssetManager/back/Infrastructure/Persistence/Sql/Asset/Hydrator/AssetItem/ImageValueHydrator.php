<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItem;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Akeneo\AssetManager\Domain\Model\Attribute\ImageAttribute;
use Akeneo\AssetManager\Infrastructure\Persistence\Sql\Asset\Hydrator\AssetItemHydrator;

/**
 * RecordItem Value hydrator for value of type "Image".
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class ImageValueHydrator implements ValueHydratorInterface
{
    /** @var ImagePreviewUrlGenerator */
    private $imagePreviewUrlGenerator;

    public function __construct(ImagePreviewUrlGenerator $imagePreviewUrlGenerator)
    {
        $this->imagePreviewUrlGenerator = $imagePreviewUrlGenerator;
    }

    public function supports(AbstractAttribute $attribute): bool
    {
        return $attribute instanceof ImageAttribute;
    }

    public function hydrate($normalizedValue, AbstractAttribute $attribute, array $context = []): array
    {
        $attributeIdentifier = $attribute->getIdentifier()->stringValue();
        $normalizedValue['data'] = $this->imagePreviewUrlGenerator->generate(
            $normalizedValue['data']['filePath'],
            $attributeIdentifier,
            AssetItemHydrator::THUMBNAIL_PREVIEW_TYPE
        );

        return $normalizedValue;
    }
}
