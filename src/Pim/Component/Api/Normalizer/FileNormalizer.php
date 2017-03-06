<?php

namespace Pim\Component\Api\Normalizer;

use Akeneo\Component\FileStorage\Model\FileInfoInterface;
use Pim\Component\Api\Hal\Link;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileNormalizer implements NormalizerInterface
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
    public function normalize($attribute, $format = null, array $context = [])
    {
        $standardFile = $this->stdNormalizer->normalize($attribute, 'standard', $context);
        $apiFile = $standardFile;

        $route = $this->router->generate(
            'pim_api_media_file_download',
            ['code' => $apiFile['code']],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $link = new Link('download', $route);
        $apiFile['_links'] = $link->toArray();

        return $apiFile;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FileInfoInterface && 'external_api' === $format;
    }
}
