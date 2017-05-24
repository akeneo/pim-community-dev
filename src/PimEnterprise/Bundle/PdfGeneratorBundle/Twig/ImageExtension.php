<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\PdfGeneratorBundle\Twig;

use Pim\Bundle\PdfGeneratorBundle\Twig\ImageExtension as BaseImageExtension;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * This class manage images from product values to get paths for render in PDF. It extends CE class to add path
 * finding for pim_assets_collection attribute type.
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 *
 * @deprecated Will be removed in 1.8
 */
class ImageExtension extends BaseImageExtension
{
    /**
     * {@inheritdoc}
     */
    public function getImagePath(ProductInterface $product, AttributeInterface $attribute, $locale, $scope)
    {
        $result = parent::getImagePath($product, $attribute, $locale, $scope);

        if (null !== $result) {
            return $result;
        }

        $productValue = $product->getValue($attribute->getCode(), $locale, $scope);

        foreach ($productValue->getAssets() as $asset) {
            foreach ($asset->getReferences() as $reference) {
                if (null !== $reference->getFileInfo() && null !== $reference->getFileInfo()->getKey()) {
                    return sprintf('media/cache/thumbnail_small/%s', $reference->getFileInfo()->getKey());
                }
            }
        }

        return null;
    }
}
