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

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Normalizer;

use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetSingleLinkValue;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class AssetSingleLinkValueNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    private $supportedFormats = ['indexing_product', 'indexing_product_and_product_model'];

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value): ?string
    {
        return (null !== $value->getData()) ? (string) $value->getData() : null;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AssetSingleLinkValue && in_array($format, $this->supportedFormats);
    }
}
