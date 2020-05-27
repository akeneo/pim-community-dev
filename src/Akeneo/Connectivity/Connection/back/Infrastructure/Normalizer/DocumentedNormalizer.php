<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Normalizer;

use Akeneo\Pim\Enrichment\Component\DocumentedExceptionInterface;
use Akeneo\Pim\Enrichment\Component\Product\Exception\UnknownAttributeException;
use Akeneo\Tool\Component\Api\Exception\DocumentedHttpException;
use Akeneo\Tool\Component\Api\Hal\Link;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DocumentedNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($exception, $format = null, array $context = []): array
    {
        $this->checkArgumentValidity($exception, $format);

        $data = [
            'code'    => $exception->getStatusCode(),
            'message' => $exception->getMessage()
        ];

        $link = new Link('documentation', $exception->getHref());
        $data['_links'] = $link->toArray();

        /** @var DocumentedExceptionInterface $documented */
        $documented = $exception->getPrevious();
        $data['documentation'] = $documented->getDocumentation();

        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($exception, $format = null): bool
    {
        return $exception instanceof DocumentedHttpException &&
            $exception->getPrevious() instanceof DocumentedExceptionInterface;
    }

    private function checkArgumentValidity($exception, $format = null): void
    {
        if (!$this->supportsNormalization($exception, $format)) {
            throw new \InvalidArgumentException(
                sprintf(
                    '"%s" does not support normalization of "%s".',
                    self::class,
                    get_class($exception)
                )
            );
        }
    }
}
