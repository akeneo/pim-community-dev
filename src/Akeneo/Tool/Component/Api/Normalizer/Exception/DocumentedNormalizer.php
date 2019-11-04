<?php

namespace Akeneo\Tool\Component\Api\Normalizer\Exception;

use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a DocumentedHttpException with a link to the documentation
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class DocumentedNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($exception, $format = null, array $context = [])
    {
        $data = [
            'code'    => $exception->getStatusCode(),
            'message' => $exception->getMessage()
        ];

        $link = new Link('documentation', $exception->getHref());
        $data['_links'] = $link->toArray();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($exception, $format = null)
    {
        return $exception instanceof DocumentedHttpException;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
