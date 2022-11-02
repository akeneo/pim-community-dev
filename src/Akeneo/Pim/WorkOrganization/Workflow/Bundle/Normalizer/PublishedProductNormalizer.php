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
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Published product normalizer
 *
 * @author Christophe Chausseray <christophe.chausseray@akeneo.com>
 */
class PublishedProductNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    const FIELD_ASSOCIATIONS = 'associations';

    /** @var NormalizerInterface */
    private $propertiesNormalizer;

    /** @var NormalizerInterface */
    private $associationsNormalizer;

    public function __construct(
        NormalizerInterface $propertiesNormalizer,
        NormalizerInterface $associationsNormalizer
    ) {
        $this->propertiesNormalizer = $propertiesNormalizer;
        $this->associationsNormalizer = $associationsNormalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($publishedProduct, $format = null, array $context = [])
    {
        $data = $this->propertiesNormalizer->normalize($publishedProduct, $format, $context);
        $data[self::FIELD_ASSOCIATIONS] = $this->associationsNormalizer->normalize($publishedProduct, $format, $context);

        $originalProduct = $publishedProduct->getOriginalProduct();

        if ($originalProduct->isVariant()) {
            $data['parent'] = $originalProduct->getParent()->getCode();
        }

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof PublishedProductInterface && 'standard' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
