<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\PreviewGenerator;

use Akeneo\Pim\Enrichment\Bundle\File\DefaultImageProviderInterface;
use Akeneo\Pim\Enrichment\Bundle\File\FileTypes;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\Url\MediaType;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class ImageGenerator implements PreviewGeneratorInterface
{
    /** @var DataManager  */
    private $dataManager;

    /** @var CacheManager  */
    private $cacheManager;

    /** @var FilterManager  */
    private $filterManager;

    /** @var DefaultImageProviderInterface  */
    private $defaultImageProvider;

    /** @var ResolverInterface  */
    private $resolver;

    public function __construct(
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        DefaultImageProviderInterface $defaultImageProvider,
        ResolverInterface $resolver
    ) {
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->defaultImageProvider = $defaultImageProvider;
        $this->resolver = $resolver;
    }

    public function supports(string $data, UrlAttribute $attribute, string $type): bool
    {
        return MediaType::IMAGE === $attribute->getMediaType()->normalize();
    }

    public function generate(string $data, UrlAttribute $attribute, string $type): string
    {
        $url = sprintf('%s%s%s', $attribute->getPrefix()->normalize(), $data, $attribute->getSuffix()->normalize()) ;
        $httpCode = $this->getHttpStatusCode($url);

        if (404 === $httpCode) {
            return $this->defaultImageProvider->getImageUrl(FileTypes::MISC, $type);
        }

//        return $this->imagineController->filterAction($request, $filename, $filter);

        if (!$this->cacheManager->isStored($url, $type, $this->resolver)) {
            try {
                $binary = $this->dataManager->find($type, $url);
            } catch (NotLoadableException $e) {
                throw new NotFoundHttpException('Source image could not be found', $e);
            }

            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $type),
                $url,
                $type,
                $this->resolver
            );
        }

        return $this->cacheManager->resolve($url, $type);
    }

    private function getHttpStatusCode($url): string
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode;
    }
}
