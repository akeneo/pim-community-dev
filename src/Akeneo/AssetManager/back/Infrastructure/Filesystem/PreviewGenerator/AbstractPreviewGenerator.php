<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPreviewGenerator implements PreviewGeneratorInterface
{
    /** @var DefaultImageProviderInterface */
    protected $defaultImageProvider;

    /** @var DataManager */
    protected $dataManager;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var FilterManager */
    protected $filterManager;

    public function __construct(
        DataManager $dataManager,
        CacheManager $cacheManager,
        FilterManager $filterManager,
        DefaultImageProviderInterface $defaultImageProvider
    ) {
        $this->dataManager = $dataManager;
        $this->cacheManager = $cacheManager;
        $this->filterManager = $filterManager;
        $this->defaultImageProvider = $defaultImageProvider;
    }

    abstract public function supports(string $data, AbstractAttribute $attribute, string $type): bool;

    public function generate(string $data, AbstractAttribute $attribute, string $type): string
    {
        $url = $this->generateUrl($data, $attribute);
        $previewType = $this->getPreviewType($type);

        if (!$this->cacheManager->isStored($url, $previewType)) {
            try {
                $binary = $this->dataManager->find($previewType, $url);
            } catch (NotLoadableException $e) {
                // Should change depending on the preview type
                return $this->defaultImageProvider->getImageUrl($this->defaultImage(), $previewType);
            } catch (\LogicException $e) { //Here we catch different levels of exception to display a different default image in the future
                // Trigerred when the mime type was not the good one
                // Should change depending on the preview type
                return $this->defaultImageProvider->getImageUrl($this->defaultImage(), $previewType);
            } catch (\Exception $e) {
                // Triggered When a general exception arrised
                // Should change depending on the preview type
                return $this->defaultImageProvider->getImageUrl($this->defaultImage(), $previewType);
            }

            try {
                $file = $this->filterManager->applyFilter($binary, $previewType);
            } catch (\Exception $e) {
                // Triggered When a general exception arrised
                // Should change depending on the preview type
                return $this->defaultImageProvider->getImageUrl($this->defaultImage(), $previewType);
            }

            $this->cacheManager->store(
                $file,
                $url,
                $previewType
            );
        }

        return $this->cacheManager->resolve($url, $previewType);
    }

    abstract protected function getPreviewType(string $type): string;

    abstract protected function generateUrl(string $data, AbstractAttribute $attribute): string;

    abstract protected function defaultImage(): string;
}
