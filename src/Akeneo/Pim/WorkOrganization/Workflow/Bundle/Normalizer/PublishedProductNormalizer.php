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

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Normalizer;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Published product normalizer
 *
 * @author Christophe Chausseray <christophe.chausseray@akeneo.com>
 */
class PublishedProductNormalizer implements NormalizerInterface
{
    /** @var NormalizerInterface */
    protected $productNormalizer;

    /** @var string[] */
    protected $supportedFormat = ['standard'];

    /**
     * @param NormalizerInterface $productNormalizer
     */
    public function __construct(NormalizerInterface $productNormalizer)
    {
        $this->productNormalizer = $productNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($publishedProduct, $format = null, array $context = [])
    {
        $normalizedProduct = $this->productNormalizer->normalize($publishedProduct, $format, $context);
        $originalProduct = $publishedProduct->getOriginalProduct();

        if ($originalProduct->isVariant()) {
            $normalizedProduct['parent'] = $originalProduct->getParent()->getCode();
        }

        return $normalizedProduct;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof PublishedProductInterface && in_array($format, $this->supportedFormat);
    }
}
