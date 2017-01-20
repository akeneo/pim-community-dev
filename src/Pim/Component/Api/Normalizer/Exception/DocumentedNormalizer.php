<?php

namespace Pim\Component\Api\Normalizer\Exception;

use Pim\Component\Api\Exception\DocumentedHttpException;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalize a DocumentedHttpException with a link to the documentation
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 */
class DocumentedNormalizer implements NormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($exception, $format = null, array $context = [])
    {
        // TODO: call new Link() when pagination will be done
        $data = [
            'code'    => $exception->getStatusCode(),
            'message' => $exception->getMessage(),
            '_links'  => [
                'documentation' => [
                    'href' => $exception->getHref()
                ]
            ]
        ];

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($exception, $format = null)
    {
        return $exception instanceof DocumentedHttpException;
    }
}
