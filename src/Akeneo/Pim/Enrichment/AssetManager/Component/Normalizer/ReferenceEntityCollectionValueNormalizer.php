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

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\AbstractProductValueNormalizer;
use Akeneo\Pim\Enrichment\AssetManager\Component\Value\AssetMultipleLinkValue;
use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize assets in an Asset Family Collection.
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AssetMultipleLinkValueNormalizer extends AbstractProductValueNormalizer implements NormalizerInterface
{
    private $supportedFormats = ['indexing_product', 'indexing_product_and_product_model'];

    /**
     * {@inheritdoc}
     */
    protected function getNormalizedData(ValueInterface $value): array
    {
        $assets = $value->getData();
        $assetsCode = array_map(function (AssetCode $assetCode) {
            return $assetCode->__toString();
        }, $assets);

        return $assetsCode;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof AssetMultipleLinkValue && in_array($format, $this->supportedFormats);
    }
}
