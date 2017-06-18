<?php

namespace Pim\Bundle\PdfGeneratorBundle\Twig;

use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductInterface;

/**
 * This class manages images from product values to get paths for render in PDF.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated Will be removed in 1.8
 */
class ImageExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('image_path', [$this, 'getImagePath']),
        ];
    }

    /**
     * Returns the image path for an attribute of a product. If no image is found, returns null.
     *
     * @param ProductInterface   $product
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @return string|null
     */
    public function getImagePath(ProductInterface $product, AttributeInterface $attribute, $locale, $scope)
    {
        $productValue = $product->getValue($attribute->getCode(), $locale, $scope);

        $path = null;
        if (null !== $productValue->getMedia() && null !== $productValue->getMedia()->getKey()) {
            $path = sprintf('media/cache/thumbnail/%s', $productValue->getMedia()->getKey());
        }

        return $path;
    }
}
