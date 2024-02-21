<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;

/**
 * This Normalizer will normalize the "image" field, to return paths to get the image to display.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ImageNormalizer
{
    /** @var FileNormalizer */
    protected $fileNormalizer;

    /**
     * @param FileNormalizer $fileNormalizer
     */
    public function __construct(FileNormalizer $fileNormalizer)
    {
        $this->fileNormalizer = $fileNormalizer;
    }

    /**
     * Normalizes a value interface to display an image
     *
     * @param ValueInterface|null $value
     * @param string|null         $localeCode
     * @param string|null         $channelCode
     *
     * @return array|null
     */
    public function normalize(?ValueInterface $value, ?string $localeCode = null, ?string $channelCode = null): ?array
    {
        if (null === $value || null === $value->getData()) {
            return null;
        }

        return $this->fileNormalizer->normalize($value->getData());
    }
}
