<?php

namespace Akeneo\Pim\Enrichment\Bundle\File;

use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Model\Binary;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Default image provider implementation
 * Uses Imagine lib to apply filters on images and cache it
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DefaultImageProvider implements DefaultImageProviderInterface
{
    /** @var FilterManager */
    protected $filterManager;

    /** @var CacheManager */
    protected $cacheManager;

    /** @var array */
    protected $defaultImages;

    /**
     * @param FilterManager $filterManager
     * @param CacheManager  $cacheManager
     * @param array         $defaultImages
     */
    public function __construct(FilterManager $filterManager, CacheManager $cacheManager, array $defaultImages)
    {
        $this->filterManager = $filterManager;
        $this->cacheManager = $cacheManager;

        $resolver = new OptionsResolver();
        $this->configureDefaultImagesOptions($resolver);
        foreach ($defaultImages as $fileType => $defaultImage) {
            $this->defaultImages[$fileType] = $resolver->resolve($defaultImage);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getImageUrl($fileType, $filter)
    {
        $fileKey = sprintf('%s_default_image', $fileType);
        if (!$this->cacheManager->isStored($fileKey, $filter)) {
            $binary = $this->getImageBinary($fileType);
            $this->cacheManager->store(
                $this->filterManager->applyFilter($binary, $filter),
                $fileKey,
                $filter
            );
        }

        return $this->cacheManager->resolve($fileKey, $filter);
    }

    /**
     * Return a Binary instance that embed the image corresponding to the specified file type
     *
     * @param string $fileType
     *
     * @throws \InvalidArgumentException
     *
     * @return Binary
     */
    protected function getImageBinary($fileType)
    {
        if (isset($this->defaultImages[$fileType])) {
            $image = $this->defaultImages[$fileType];

            return new Binary(file_get_contents($image['path']), $image['mime_type'], $image['extension']);
        }

        throw new \InvalidArgumentException(sprintf('No default image is defined for file type "%s"', $fileType));
    }

    /**
     * Ensure $defaultImages parameter validity
     *
     * @param OptionsResolver $resolver
     */
    protected function configureDefaultImagesOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['path', 'mime_type', 'extension']);
    }
}
