<?php

namespace Akeneo\Tool\Component\Api\Normalizer;

use Akeneo\Tool\Component\Api\Hal\Link;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{
    /** @var NormalizerInterface */
    protected $stdNormalizer;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param NormalizerInterface $stdNormalizer
     * @param RouterInterface     $router
     */
    public function __construct(NormalizerInterface $stdNormalizer, RouterInterface $router)
    {
        $this->stdNormalizer = $stdNormalizer;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($file, $format = null, array $context = [])
    {
        $normalizedFile = $this->stdNormalizer->normalize($file, 'standard', $context);

        $route = $this->router->generate(
            'pim_api_media_file_download',
            ['code' => $normalizedFile['code']],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $link = new Link('download', $route);
        $normalizedFile['_links'] = $link->toArray();

        return $normalizedFile;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInfoInterface && 'external_api' === $format;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
